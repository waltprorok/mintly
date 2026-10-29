<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class TransactionCsvImporter
{
    /**
     * @throws Throwable
     */
    public function import(
        int $userId,
        string $path,
        string $accountType = 'auto',
        string $amountConvention = 'negative_expense'
    ): array {
        if (! in_array($accountType, ['auto', 'bank', 'credit_card'], true)) {
            throw new RuntimeException('Invalid CSV account type.');
        }

        if (! in_array($amountConvention, ['negative_expense', 'positive_expense'], true)) {
            throw new RuntimeException('Invalid CSV amount convention.');
        }

        $rows = $this->readCsv($path);

        if ($rows->isEmpty()) {
            throw new RuntimeException('The CSV file is empty.');
        }

        $layout = $this->detectLayout(
            rows: $rows,
            accountType: $accountType,
            amountConvention: $amountConvention,
        );

        $categories = Category::query()
            ->where('user_id', $userId)
            ->get();

        return DB::transaction(function () use ($userId, $rows, $layout, $categories) {
            $created = 0;
            $updated = 0;
            $duplicates = 0;
            $transfers = 0;
            $unmatched = 0;

            foreach ($rows as $row) {
                $normalized = $this->normalizeRow(
                    row: $row,
                    layout: $layout,
                );

                if ($normalized === null) {
                    $transfers++;
                    continue;
                }

                $existing = $this->findExistingTransaction($userId, $normalized);

                if ($existing !== null) {
                    if ($this->isExactDuplicate($existing, $normalized)) {
                        $duplicates++;
                        continue;
                    }

                    // Only reconcile an unpaid planned monthly transaction.
                    // Never overwrite an already-paid charge with a different one.
                    if ($existing->recurring_rule === 'monthly') {
                        $existing->update([
                            'amount' => $normalized['amount'],
                            'due_at' => $normalized['date'],
                            'description' => $normalized['description'],
                            'is_paid' => true,
                        ]);
                        $updated++;
                        continue;
                    }
                }

                $category = $this->resolveCategory(
                    userId: $userId,
                    categories: $categories,
                    transaction: $normalized,
                );

                if (! $category) {
                    $unmatched++;
                }

                Transaction::create([
                    'user_id' => $userId,
                    'type' => $normalized['type'],
                    'category_id' => $category?->id,
                    'amount' => $normalized['amount'],
                    'due_at' => $normalized['date'],
                    'description' => $normalized['description'],
                    'merchant' => $normalized['merchant'],
                    'payment_method' => $normalized['payment_method'],
                    'is_recurring' => false,
                    'recurring_rule' => 'once',
                    'is_paid' => true,
                    'notes' => null,
                ]);

                $created++;
            }

            return [
                'created' => $created,
                'updated' => $updated,
                'duplicates' => $duplicates,
                'transfers' => $transfers,
                'unmatched' => $unmatched,
            ];
        });
    }

    private function readCsv(string $path): Collection
    {
        $handle = fopen($path, 'r');

        if (! $handle) {
            throw new RuntimeException('Unable to open CSV file.');
        }

        try {
            $headers = fgetcsv($handle);

            if (! $headers) {
                return collect();
            }

            $headers = array_map(fn($header) => trim((string) $header), $headers);
            $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);

            if (count($headers) !== count(array_unique($headers))) {
                throw new RuntimeException('The CSV contains duplicate column headers.');
            }

            $rows = collect();

            while (($values = fgetcsv($handle)) !== false) {
                $values = array_slice($values, 0, count($headers));
                $values = array_pad($values, count($headers), null);
                $row = array_combine($headers, $values);

                if (collect($row)
                    ->filter(fn($value) => trim((string) $value) !== '')
                    ->isEmpty()) {
                    continue;
                }

                $rows->push($row);
            }

            return $rows;
        } finally {
            fclose($handle);
        }
    }

    private function detectLayout(Collection $rows, string $accountType, string $amountConvention): array
    {
        $sample = $rows->take(10);
        $headers = array_keys($sample->first());
        $dateColumns = [];
        $numericColumns = [];
        $textColumns = [];
        $directionColumn = null;
        $transactionTypeColumn = null;

        $debitColumn = $this->findHeader($headers, [
            'Debit', 'Debit Amount', 'Withdrawal', 'Withdrawals', 'Money Out',
        ]);
        $creditColumn = $this->findHeader($headers, [
            'Credit', 'Credit Amount', 'Deposit', 'Deposits', 'Money In',
        ]);
        $hasSplitAmounts = $debitColumn !== null && $creditColumn !== null;

        foreach ($headers as $header) {
            $values = $sample->pluck($header)
                ->filter(fn($value) => trim((string) $value) !== '');

            if ($values->isEmpty()) {
                continue;
            }

            if ($hasSplitAmounts && in_array($header, [$debitColumn, $creditColumn], true)) {
                continue;
            }

            $numericMatches = $values->filter(
                fn($value) => $this->looksLikeNumber($value)
            )->count();

            if ($numericMatches / $values->count() >= 0.70) {
                $numericColumns[] = $header;
                continue;
            }

            $dateMatches = $values->filter(
                fn($value) => $this->looksLikeDate($value)
            )->count();

            if ($dateMatches / $values->count() >= 0.70) {
                $dateColumns[] = $header;
                continue;
            }

            $directionMatches = $values->filter(fn($value) => in_array(
                Str::upper(trim((string) $value)), ['DEBIT', 'CREDIT'], true
            ))->count();

            if ($directionMatches / $values->count() >= 0.50) {
                $directionColumn = $header;
                continue;
            }

            $typeMatches = $values->filter(fn($value) => in_array(
                Str::upper(trim((string) $value)),
                ['SALE', 'PAYMENT', 'RETURN', 'REFUND', 'ADJUSTMENT'],
                true
            ))->count();

            if ($typeMatches / $values->count() >= 0.40) {
                $transactionTypeColumn = $header;
                continue;
            }

            $textColumns[] = $header;
        }

        $dateColumn = $this->findHeader($dateColumns, [
            'Transaction Date', 'Trans. Date', 'Date',
            'Posting Date', 'Posted Date', 'Post Date',
        ]) ?? ($dateColumns[0] ?? null);

        if (! $dateColumn) {
            throw new RuntimeException('Unable to identify a transaction date column.');
        }

        $amountColumn = null;
        if (! $hasSplitAmounts) {
            if (empty($numericColumns)) {
                throw new RuntimeException('Unable to identify a transaction amount column.');
            }
            $amountColumn = $this->findAmountColumn(
                sample: $sample,
                columns: $numericColumns,
            );
        }

        $descriptionColumn = $this->findHeader($textColumns, [
            'Description', 'Transaction Description', 'Merchant',
            'Merchant Name', 'Payee', 'Name', 'Details', 'Memo',
        ]);

        if (! $descriptionColumn) {
            $descriptionColumn = $this->findDescriptionColumn(
                sample: $sample,
                columns: $textColumns,
            );
        }

        if (! $descriptionColumn) {
            throw new RuntimeException('Unable to identify a transaction description column.');
        }

        $categoryColumn = collect($headers)->first(
            fn($header) => str_contains(Str::lower($header), 'category')
        );

        if ($accountType !== 'auto') {
            $format = $accountType;
        } else {
            $format = $directionColumn
                ? 'bank'
                : ($transactionTypeColumn ? 'credit_card' : 'generic');
        }

        return [
            'format' => $format,
            'date' => $dateColumn,
            'description' => $descriptionColumn,
            'amount' => $amountColumn,
            'debit' => $hasSplitAmounts ? $debitColumn : null,
            'credit' => $hasSplitAmounts ? $creditColumn : null,
            'direction' => $directionColumn,
            'transaction_type' => $transactionTypeColumn,
            'category' => $categoryColumn,
            'amount_convention' => $amountConvention,
        ];
    }

    private function normalizeRow(array $row, array $layout): ?array
    {
        $description = trim((string) ($row[$layout['description']] ?? ''));

        if ($this->isTransfer($row, $layout, $description)) {
            return null;
        }

        $amount = $this->getTransactionAmount(row: $row, layout: $layout);
        $type = $this->determineTransactionType(
            row: $row,
            layout: $layout,
            amount: $amount,
        );

        return [
            'date' => $this->parseDate($row[$layout['date']] ?? null),
            'type' => $type,
            'amount' => abs($amount),
            'merchant' => MerchantNormalizer::display($description),
            'description' => $description,
            'external_category' => $layout['category']
                ? trim((string) ($row[$layout['category']] ?? ''))
                : null,
            'payment_method' => $layout['format'] === 'credit_card'
                ? 'credit_card'
                : 'bank',
        ];
    }

    private function getTransactionAmount(array $row, array $layout): float
    {
        if ($layout['debit'] && $layout['credit']) {
            $debitValue = trim((string) ($row[$layout['debit']] ?? ''));
            $creditValue = trim((string) ($row[$layout['credit']] ?? ''));
            $hasDebit = $debitValue !== '';
            $hasCredit = $creditValue !== '';

            if ($hasDebit === $hasCredit) {
                throw new RuntimeException(
                    'Invalid transaction: expected an amount in either Debit or Credit.'
                );
            }

            if ($hasDebit) {
                return -abs($this->parseNumber($debitValue));
            }

            return abs($this->parseNumber($creditValue));
        }

        return $this->parseNumber($row[$layout['amount']] ?? null);
    }

    private function determineTransactionType(array $row, array $layout, float $amount): string
    {
        if ($layout['transaction_type']) {
            $transactionType = Str::upper(trim(
                (string) ($row[$layout['transaction_type']] ?? '')
            ));

            if ($transactionType === 'SALE') {
                return 'expense';
            }

            if (in_array($transactionType, ['RETURN', 'REFUND'], true)) {
                return 'income';
            }
        }

        if ($layout['direction']) {
            $direction = Str::upper(trim(
                (string) ($row[$layout['direction']] ?? '')
            ));

            if ($direction === 'CREDIT') {
                return 'income';
            }

            if ($direction === 'DEBIT') {
                return 'expense';
            }
        }

        if ($layout['debit'] && $layout['credit']) {
            return $amount < 0 ? 'expense' : 'income';
        }

        if ($layout['amount_convention'] === 'positive_expense') {
            return $amount > 0 ? 'expense' : 'income';
        }

        return $amount < 0 ? 'expense' : 'income';
    }

    private function isTransfer(array $row, array $layout, string $description): bool
    {
        if ($layout['transaction_type']) {
            $transactionType = Str::upper(trim(
                (string) ($row[$layout['transaction_type']] ?? '')
            ));

            if ($transactionType === 'PAYMENT') {
                return true;
            }
        }

        $description = Str::lower(trim($description));

        if (str_contains($description, 'online transfer to')
            || str_contains($description, 'online transfer from')) {
            return true;
        }

        if (str_contains($description, 'payment to') && str_contains($description, 'card')) {
            return true;
        }

        if ($layout['format'] === 'credit_card') {
            $patterns = [
                'payment received', 'automatic payment', 'payment thank you',
                'payment - thank you', 'autopay payment', 'credit card autopay',
            ];

            foreach ($patterns as $pattern) {
                if (str_contains($description, $pattern)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function findHeader(array $headers, array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            foreach ($headers as $header) {
                if (Str::lower(trim($header)) === Str::lower($candidate)) {
                    return $header;
                }
            }
        }

        return null;
    }

    private function resolveCategory(int $userId, Collection $categories, array $transaction): ?Category
    {
        $mappedName = $this->mapMerchantCategory($transaction['description']);

        if ($mappedName) {
            $category = $categories->first(
                fn(Category $category) => strcasecmp(trim($category->name), $mappedName) === 0
            );

            if ($category) {
                return $category;
            }
        }

        if ($transaction['external_category']) {
            $category = $categories->first(
                fn(Category $category) => strcasecmp(
                        trim($category->name), trim($transaction['external_category'])
                    ) === 0
            );

            if ($category) {
                return $category;
            }
        }

        if ($transaction['external_category']) {
            $mappedName = $this->mapExternalCategory($transaction['external_category']);

            if ($mappedName) {
                $category = $categories->first(
                    fn(Category $category) => strcasecmp(trim($category->name), $mappedName) === 0
                );

                if ($category) {
                    return $category;
                }
            }
        }

        $merchantKey = MerchantNormalizer::normalize($transaction['merchant']);

        $previous = Transaction::query()
            ->with('category')
            ->where('user_id', $userId)
            ->whereNotNull('merchant')
            ->latest('due_at')
            ->get()
            ->first(
                fn(Transaction $existing) =>
                    MerchantNormalizer::normalize($existing->merchant) === $merchantKey
            );

        return $previous?->category;
    }

    private function mapExternalCategory(string $category): ?string
    {
        return match (Str::lower(trim($category))) {
            'groceries' => 'Groceries',
            'gas' => 'Gas',
            'shopping' => 'Shopping',
            'amazon', 'amazon purchases' => 'Amazon Purchases',
            'gifts & donations' => 'Gifts & Donations',
            'miscellaneous' => 'Miscellaneous',
            'food & drink' => 'Dining',
            'bills & utilities' => 'Utilities',
            'home' => 'House',
            default => null,
        };
    }

    private function cleanMerchant(string $description): string
    {
        $merchant = Str::of($description)
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->replaceMatches('/\s+\d{1,2}\/\d{1,2}$/', '')
            ->replaceMatches('/\b\d{3}[- ]?\d{3}[- ]?\d{4}\b/', '')
            ->replaceMatches('/\s+/', ' ')
            ->trim();

        return Str::of($merchant)->words(3, '')->title()->toString();
    }

    private function looksLikeDate(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        return preg_match(
                '/^(?:\d{1,2}\/\d{1,2}\/\d{2,4}|\d{4}-\d{1,2}-\d{1,2})$/',
                trim($value)
            ) === 1;
    }

    private function looksLikeNumber(mixed $value): bool
    {
        if ($value === null || trim((string) $value) === '') {
            return false;
        }

        $value = str_replace(['$', ',', '(', ')'], '', trim((string) $value));
        return is_numeric($value);
    }

    private function parseNumber(mixed $value): float
    {
        if ($value === null) {
            throw new RuntimeException('Transaction amount is missing.');
        }

        $value = trim((string) $value);
        $negative = str_starts_with($value, '(') && str_ends_with($value, ')');
        $value = str_replace(['$', ',', '(', ')'], '', $value);

        if (! is_numeric($value)) {
            throw new RuntimeException("Invalid transaction amount: {$value}");
        }

        $amount = (float) $value;
        return $negative ? -abs($amount) : $amount;
    }

    private function parseDate(mixed $value): Carbon
    {
        if (! $value) {
            throw new RuntimeException('Transaction date is missing.');
        }

        try {
            return Carbon::parse($value)->startOfDay();
        } catch (Throwable) {
            throw new RuntimeException("Invalid transaction date: {$value}");
        }
    }

    private function findAmountColumn(Collection $sample, array $columns): string
    {
        $amountColumn = $this->findHeader($columns, [
            'Amount', 'Transaction Amount', 'Transaction Amt', 'Total Amount',
        ]);

        if ($amountColumn) {
            return $amountColumn;
        }

        $columns = array_values(array_filter(
            $columns,
            fn($column) => ! str_contains(Str::lower($column), 'balance')
        ));

        if (count($columns) === 1) {
            return $columns[0];
        }

        if (empty($columns)) {
            throw new RuntimeException('Unable to identify a transaction amount column.');
        }

        $candidates = [];

        foreach ($columns as $column) {
            $values = $sample->pluck($column)
                ->filter(fn($value) => $this->looksLikeNumber($value))
                ->map(fn($value) => $this->parseNumber($value));

            $hasPositive = $values->contains(fn($value) => $value > 0);
            $hasNegative = $values->contains(fn($value) => $value < 0);

            if ($hasPositive && $hasNegative) {
                $candidates[] = $column;
            }
        }

        if (count($candidates) === 1) {
            return $candidates[0];
        }

        throw new RuntimeException('Unable to determine which CSV column contains transaction amounts.');
    }

    private function findDescriptionColumn(Collection $sample, array $columns): ?string
    {
        $bestColumn = null;
        $bestLength = 0;

        foreach ($columns as $column) {
            $values = $sample->pluck($column)
                ->filter(fn($value) => trim((string) $value) !== '');

            if ($values->isEmpty()) {
                continue;
            }

            $averageLength = $values
                ->map(fn($value) => strlen(trim((string) $value)))
                ->average();

            if ($averageLength > $bestLength) {
                $bestLength = $averageLength;
                $bestColumn = $column;
            }
        }

        return $bestColumn;
    }

    /**
     * Prefer exact matches. Otherwise return only a unique unpaid monthly
     * plan in the same calendar month. Ambiguous monthly plans are not
     * automatically reconciled.
     */
    private function findExistingTransaction(int $userId, array $transaction): ?Transaction
    {
        $merchantKey = MerchantNormalizer::normalize($transaction['merchant']);

        $matches = Transaction::query()
            ->where('user_id', $userId)
            ->where('type', $transaction['type'])
            ->whereYear('due_at', $transaction['date']->year)
            ->whereMonth('due_at', $transaction['date']->month)
            ->get()
            ->filter(fn(Transaction $existing) =>
                MerchantNormalizer::normalize($existing->merchant) === $merchantKey
            );

        // Prioritize existing monthly recurring transactions.
        $monthly = $matches->filter(fn(Transaction $existing) =>
            $existing->recurring_rule === 'monthly'
        );

        if ($monthly->count() === 1) {
            return $monthly->first();
        }

        // Otherwise, check for exact duplicates.
        return $matches->first(fn(Transaction $existing) =>
        $this->isExactDuplicate($existing, $transaction)
        );
    }

    private function isExactDuplicate(Transaction $existing, array $transaction): bool
    {
        return $existing->due_at->isSameDay($transaction['date'])
            && abs((float) $existing->amount - (float) $transaction['amount']) < 0.01;
    }

    private function mapMerchantCategory(string $merchant): ?string
    {
        $merchant = Str::lower(trim($merchant));

        return match (true) {
            Str::startsWith($merchant, ['amazon prime', 'amzn prime']) => 'Subscriptions',
            Str::startsWith($merchant, [
                'amazon mktpl', 'amazon.com', 'amzn mktp', 'amzn mktpl',
            ]) => 'Amazon Purchases',
            default => null,
        };
    }
}
