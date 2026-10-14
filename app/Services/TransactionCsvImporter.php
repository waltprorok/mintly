<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class TransactionCsvImporter
{
    public function import(int $userId, string $path): array
    {
        $rows = $this->readCsv($path);

        if ($rows->isEmpty()) {
            throw new RuntimeException('The CSV file is empty.');
        }

        $layout = $this->detectLayout($rows);

        $categories = Category::query()
            ->where('user_id', $userId)
            ->get();

        $created = 0;
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

            // Skip transactions already imported
            if ($this->isDuplicate($userId, $normalized)) {
                $duplicates++;

                continue;
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
            'duplicates' => $duplicates,
            'transfers' => $transfers,
            'unmatched' => $unmatched,
        ];
    }

    private function readCsv(string $path): Collection
    {
        $handle = fopen($path, 'r');

        if (! $handle) {
            throw new RuntimeException('Unable to open CSV file.');
        }

        $headers = fgetcsv($handle);

        if (! $headers) {
            fclose($handle);

            return collect();
        }

        $headers = array_map(
            fn($header) => trim((string)$header),
            $headers
        );

        $rows = collect();

        while (($values = fgetcsv($handle)) !== false) {
            /*
             * Some bank CSV exports include extra trailing blank columns.
             */
            $values = array_slice(
                $values,
                0,
                count($headers)
            );

            $values = array_pad(
                $values,
                count($headers),
                null
            );

            $row = array_combine($headers, $values);

            /*
             * Ignore completely empty CSV rows.
             */
            if (
                collect($row)
                    ->filter(fn($value) => trim((string)$value) !== '')
                    ->isEmpty()
            ) {
                continue;
            }

            $rows->push($row);
        }

        fclose($handle);

        return $rows;
    }

    private function detectLayout(Collection $rows): array
    {
        $sample = $rows->take(10);

        $headers = array_keys(
            $sample->first()
        );

        $dateColumns = [];
        $numericColumns = [];
        $textColumns = [];
        $directionColumn = null;
        $transactionTypeColumn = null;

        foreach ($headers as $header) {
            $values = $sample
                ->pluck($header)
                ->filter(fn($value) => trim((string)$value) !== '');

            if ($values->isEmpty()) {
                continue;
            }

            /*
             * Numeric detection first.
             */
            $numericMatches = $values->filter(
                fn($value) => $this->looksLikeNumber($value)
            )->count();

            if ($numericMatches / $values->count() >= 0.70) {
                $numericColumns[] = $header;

                continue;
            }

            /*
             * Date detection second.
             */
            $dateMatches = $values->filter(
                fn($value) => $this->looksLikeDate($value)
            )->count();

            if ($dateMatches / $values->count() >= 0.70) {
                $dateColumns[] = $header;

                continue;
            }

            /*
             * DEBIT / CREDIT direction.
             */
            $directionMatches = $values->filter(function ($value) {
                return in_array(
                    Str::upper(trim((string)$value)),
                    [
                        'DEBIT',
                        'CREDIT',
                    ],
                    true
                );
            })->count();

            if ($directionMatches / $values->count() >= 0.50) {
                $directionColumn = $header;

                continue;
            }

            /*
             * Credit-card transaction type.
             */
            $typeMatches = $values->filter(function ($value) {
                return in_array(
                    Str::upper(trim((string)$value)),
                    [
                        'SALE',
                        'PAYMENT',
                        'RETURN',
                        'REFUND',
                        'ADJUSTMENT',
                    ],
                    true
                );
            })->count();

            if ($typeMatches / $values->count() >= 0.40) {
                $transactionTypeColumn = $header;

                continue;
            }

            $textColumns[] = $header;
        }

        if (empty($dateColumns)) {
            throw new RuntimeException(
                'Unable to identify a transaction date column.'
            );
        }

        if (empty($numericColumns)) {
            throw new RuntimeException(
                'Unable to identify a transaction amount column.'
            );
        }

        /*
         * When multiple date columns exist, use the first one.
         *
         * For your Chase credit card CSV this means Transaction Date
         * rather than Post Date.
         */
        $dateColumn = $dateColumns[0];

        /*
         * Determine which numeric column looks most like an amount.
         *
         * Prefer a column containing both positive and negative values.
         * This helps distinguish Amount from Balance in checking exports.
         */
        $amountColumn = $this->findAmountColumn(
            sample: $sample,
            columns: $numericColumns,
        );

        /*
         * Description is normally the text column containing the
         * longest free-form values.
         */
        $descriptionColumn = $this->findDescriptionColumn(
            sample: $sample,
            columns: $textColumns,
        );

        if (! $descriptionColumn) {
            throw new RuntimeException(
                'Unable to identify a transaction description column.'
            );
        }

        /*
         * Look for a category column if one appears to exist.
         *
         * Header names are only hints here, not requirements.
         */
        $categoryColumn = collect($headers)->first(
            fn($header) => str_contains(
                Str::lower($header),
                'category'
            )
        );

        /*
         * If we found DEBIT / CREDIT values, treat it like a
         * bank/checking export.
         *
         * Otherwise, if we found Sale / Payment / Return,
         * treat it like a credit card export.
         */
        $format = $directionColumn
            ? 'bank'
            : ($transactionTypeColumn ? 'credit_card' : 'generic');

        return [
            'format' => $format,
            'date' => $dateColumn,
            'description' => $descriptionColumn,
            'amount' => $amountColumn,
            'direction' => $directionColumn,
            'transaction_type' => $transactionTypeColumn,
            'category' => $categoryColumn,
        ];
    }

    private function normalizeRow(
        array $row,
        array $layout,
    ): ?array
    {
        $description = trim(
            (string)($row[$layout['description']] ?? '')
        );

        $amount = $this->parseNumber(
            $row[$layout['amount']] ?? null
        );

        /*
         * Credit card payment.
         *
         * This is a transfer from checking to the card rather
         * than another expense.
         */
        if ($layout['transaction_type']) {
            $transactionType = Str::upper(
                trim(
                    (string)(
                        $row[$layout['transaction_type']]
                        ?? ''
                    )
                )
            );

            if ($transactionType === 'PAYMENT') {
                return null;
            }
        }

        /*
         * Bank payment to a credit card.
         *
         * Keep this rule for now because otherwise importing both
         * checking and credit card CSV files double-counts spending.
         */
        $lowerDescription = Str::lower($description);

        if (
            str_contains($lowerDescription, 'payment to')
            && str_contains($lowerDescription, 'card')
        ) {
            return null;
        }

        $type = $this->determineTransactionType(
            row: $row,
            layout: $layout,
            amount: $amount,
        );

        return [
            'date' => $this->parseDate(
                $row[$layout['date']] ?? null
            ),

            'type' => $type,

            'amount' => abs($amount),

            /*
             * Don't mutate merchants into hard-coded names.
             * Keep the source description, only normalize whitespace.
             */
            'merchant' => $this->cleanMerchant(
                $description
            ),

            'description' => $description,

            'external_category' => $layout['category']
                ? trim(
                    (string)(
                        $row[$layout['category']]
                        ?? ''
                    )
                )
                : null,

            'payment_method' => $layout['format'] === 'credit_card'
                ? 'credit_card'
                : 'bank',
        ];
    }

    private function determineTransactionType(
        array $row,
        array $layout,
        float $amount,
    ): string
    {
        /*
         * Bank exports with explicit CREDIT / DEBIT indicators.
         */
        if ($layout['direction']) {
            $direction = Str::upper(
                trim(
                    (string)(
                        $row[$layout['direction']]
                        ?? ''
                    )
                )
            );

            if ($direction === 'CREDIT') {
                return 'income';
            }

            if ($direction === 'DEBIT') {
                return 'expense';
            }
        }

        /*
         * Credit card exports commonly use negative purchases.
         */
        if ($layout['format'] === 'credit_card') {
            return $amount < 0
                ? 'expense'
                : 'income';
        }

        /*
         * Generic fallback.
         */
        return $amount < 0
            ? 'expense'
            : 'income';
    }

    private function resolveCategory(
        int        $userId,
        Collection $categories,
        array      $transaction,
    ): ?Category
    {
        /*
         * 1. Try an exact category supplied by the CSV.
         */
        if ($transaction['external_category']) {
            $category = $categories->first(
                fn(Category $category) => strcasecmp(
                        trim($category->name),
                        trim($transaction['external_category'])
                    ) === 0
            );

            if ($category) {
                return $category;
            }
        }

        /*
         * 2. Try the small generic mapping between common
         * financial categories and Mintly categories.
         */
        if ($transaction['external_category']) {
            $mappedName = $this->mapExternalCategory(
                $transaction['external_category']
            );

            if ($mappedName) {
                $category = $categories->first(
                    fn(Category $category) => strcasecmp(
                            trim($category->name),
                            $mappedName
                        ) === 0
                );

                if ($category) {
                    return $category;
                }
            }
        }

        /*
         * 3. Reuse a category if this exact merchant has already
         * been categorized by this user.
         */
        $merchantKey = $this->merchantKey(
            $transaction['merchant']
        );

        $previous = Transaction::query()
            ->with('category')
            ->where('user_id', $userId)
            ->whereNotNull('merchant')
            ->latest('due_at')
            ->get()
            ->first(
                fn(Transaction $existing) => $this->merchantKey($existing->merchant)
                    === $merchantKey
            );

        return $previous?->category;
    }

    private function mapExternalCategory(
        string $category
    ): ?string
    {
        return match (Str::lower(trim($category))) {
            'groceries' => 'Groceries',
            'gas' => 'Gas',
            'shopping' => 'Shopping',
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

        return Str::of($merchant)
            ->words(3, '')
            ->title()
            ->toString();
    }

    private function merchantKey(
        ?string $merchant
    ): string
    {
        return Str::of($merchant ?? '')
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '')
            ->toString();
    }

    private function looksLikeDate(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        $value = trim($value);

        return preg_match(
                '/^(?:\d{1,2}\/\d{1,2}\/\d{2,4}|\d{4}-\d{1,2}-\d{1,2})$/',
                $value
            ) === 1;
    }

    private function looksLikeNumber(
        mixed $value
    ): bool
    {
        if ($value === null || trim((string)$value) === '') {
            return false;
        }

        $value = str_replace(
            [
                '$',
                ',',
                '(',
                ')',
            ],
            '',
            trim((string)$value)
        );

        return is_numeric($value);
    }

    private function parseNumber(
        mixed $value
    ): float
    {
        if ($value === null) {
            throw new RuntimeException(
                'Transaction amount is missing.'
            );
        }

        $value = trim((string)$value);

        $negative = str_starts_with($value, '(')
            && str_ends_with($value, ')');

        $value = str_replace(
            [
                '$',
                ',',
                '(',
                ')',
            ],
            '',
            $value
        );

        if (! is_numeric($value)) {
            throw new RuntimeException(
                "Invalid transaction amount: {$value}"
            );
        }

        $amount = (float)$value;

        return $negative
            ? -abs($amount)
            : $amount;
    }

    private function parseDate(
        mixed $value
    ): Carbon
    {
        if (! $value) {
            throw new RuntimeException(
                'Transaction date is missing.'
            );
        }

        try {
            return Carbon::parse($value)
                ->startOfDay();
        } catch (Throwable) {
            throw new RuntimeException(
                "Invalid transaction date: {$value}"
            );
        }
    }

    private function findAmountColumn(
        Collection $sample,
        array      $columns,
    ): string
    {
        if (count($columns) === 1) {
            return $columns[0];
        }

        foreach ($columns as $column) {
            $values = $sample
                ->pluck($column)
                ->filter(fn($value) => $this->looksLikeNumber($value))
                ->map(fn($value) => $this->parseNumber($value));

            $hasPositive = $values->contains(
                fn($value) => $value > 0
            );

            $hasNegative = $values->contains(
                fn($value) => $value < 0
            );

            /*
             * Amount columns commonly contain both money-in
             * and money-out values. Balance columns usually don't.
             */
            if ($hasPositive && $hasNegative) {
                return $column;
            }
        }

        /*
         * Fall back to the first numeric column.
         */
        return $columns[0];
    }

    private function findDescriptionColumn(
        Collection $sample,
        array      $columns,
    ): ?string
    {
        $bestColumn = null;
        $bestLength = 0;

        foreach ($columns as $column) {
            $values = $sample
                ->pluck($column)
                ->filter(
                    fn($value) => trim((string)$value) !== ''
                );

            if ($values->isEmpty()) {
                continue;
            }

            $averageLength = $values
                ->map(
                    fn($value) => strlen(trim((string)$value))
                )
                ->average();

            if ($averageLength > $bestLength) {
                $bestLength = $averageLength;
                $bestColumn = $column;
            }
        }

        return $bestColumn;
    }

    private function isDuplicate(
        int   $userId,
        array $transaction,
    ): bool
    {
        return Transaction::query()
            ->where('user_id', $userId)
            ->where('type', $transaction['type'])
            ->whereDate(
                'due_at',
                $transaction['date']
            )
            ->where(
                'amount',
                $transaction['amount']
            )
            ->whereRaw(
                'LOWER(merchant) = ?',
                [
                    Str::lower(
                        $transaction['merchant']
                    ),
                ]
            )
            ->exists();
    }
}
