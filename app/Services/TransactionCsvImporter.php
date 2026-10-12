<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;

class TransactionCsvImporter
{
    public function import(int $userId, string $path): array
    {
        $rows = $this->readCsv($path);

        if ($rows->isEmpty()) {
            throw new RuntimeException('The CSV file is empty.');
        }

        $headers = array_keys($rows->first());

        $format = $this->detectFormat($headers);

        $categories = Category::query()
            ->where('user_id', $userId)
            ->get();

        $created = 0;
        $duplicates = 0;
        $transfers = 0;
        $unmatched = 0;

        foreach ($rows as $row) {
            $normalized = match ($format) {
                'chase_credit_card' => $this->normalizeCreditCard($row),
                'chase_bank' => $this->normalizeBank($row),
            };

            if ($normalized === null) {
                $transfers++;

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

            if ($this->isDuplicate($userId, $normalized)) {
                $duplicates++;

                continue;
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
            fn ($header) => trim((string) $header),
            $headers
        );

        $rows = collect();

        while (($values = fgetcsv($handle)) !== false) {
            // Chase checking exports can occasionally have an extra blank field.
            $values = array_slice($values, 0, count($headers));

            $values = array_pad(
                $values,
                count($headers),
                null
            );

            $rows->push(
                array_combine($headers, $values)
            );
        }

        fclose($handle);

        return $rows;
    }

    private function detectFormat(array $headers): string
    {
        if (
            in_array('Transaction Date', $headers, true)
            && in_array('Post Date', $headers, true)
            && in_array('Category', $headers, true)
        ) {
            return 'chase_credit_card';
        }

        if (
            in_array('Details', $headers, true)
            && in_array('Posting Date', $headers, true)
            && in_array('Balance', $headers, true)
        ) {
            return 'chase_bank';
        }

        throw new RuntimeException(
            'This CSV format is not currently supported.'
        );
    }

    private function normalizeCreditCard(array $row): ?array
    {
        /*
         * Example:
         *
         * Transaction Date = 09/29/2026
         * Description      = SHIPT* ORDER
         * Category         = Groceries
         * Type             = Sale
         * Amount           = -160.94
         */

        if (strcasecmp(trim($row['Type'] ?? ''), 'Payment') === 0) {
            return null;
        }

        $amount = (float) $row['Amount'];

        return [
            'date' => Carbon::createFromFormat(
                'm/d/Y',
                trim($row['Transaction Date'])
            )->startOfDay(),

            'type' => $amount < 0
                ? 'expense'
                : 'income',

            'amount' => abs($amount),

            'merchant' => $this->normalizeMerchant(
                $row['Description']
            ),

            'description' => trim($row['Description']),

            'external_category' => trim(
                $row['Category'] ?? ''
            ),

            'payment_method' => 'credit_card',
        ];
    }

    private function normalizeBank(array $row): ?array
    {
        /*
         * Skip payments from checking -> credit card.
         *
         * Those purchases already exist in the credit-card CSV
         * and should not become expenses again.
         */
        if (
            str_contains(
                Str::lower($row['Description'] ?? ''),
                'payment to chase card'
            )
        ) {
            return null;
        }

        $amount = (float) $row['Amount'];

        return [
            'date' => Carbon::createFromFormat(
                'm/d/Y',
                trim($row['Posting Date'])
            )->startOfDay(),

            'type' => strtoupper(trim($row['Details'])) === 'CREDIT'
                ? 'income'
                : 'expense',

            'amount' => abs($amount),

            'merchant' => $this->normalizeMerchant(
                $row['Description']
            ),

            'description' => trim($row['Description']),

            'external_category' => null,

            'payment_method' => 'bank',
        ];
    }

    private function resolveCategory(
        int $userId,
        Collection $categories,
        array $transaction,
    ): ?Category {
        /*
         * 1. Reuse the user's existing Mintly category for this merchant.
         *
         * This should be the strongest signal because it represents
         * how this specific user previously categorized the merchant.
         */
        $merchantKey = $this->merchantKey($transaction['merchant']);

        $previousTransactions = Transaction::query()
            ->with('category')
            ->where('user_id', $userId)
            ->whereNotNull('merchant')
            ->latest('due_at')
            ->get();

        $previous = $previousTransactions->first(
            fn (Transaction $existing) =>
                $this->merchantKey($existing->merchant) === $merchantKey
        );

        if ($previous?->category) {
            return $previous->category;
        }

        /*
         * 2. Exact CSV category -> Mintly category.
         *
         * Mostly useful for credit card CSVs because Chase provides
         * a Category column there.
         */
        if ($transaction['external_category']) {
            $category = $categories->first(
                fn (Category $category) =>
                    strcasecmp(
                        trim($category->name),
                        trim($transaction['external_category'])
                    ) === 0
            );

            if ($category) {
                return $category;
            }
        }

        /*
         * 3. Chase category -> Mintly category.
         */
        if ($transaction['external_category']) {
            $mappedName = $this->mapExternalCategory(
                $transaction['external_category']
            );

            if ($mappedName) {
                $category = $categories->first(
                    fn (Category $category) =>
                        strcasecmp(
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
         * 4. Known merchant -> Mintly category.
         *
         * Primarily useful for bank CSVs, which do not contain
         * a Category column.
         */
        $mappedName = $this->mapMerchantCategory(
            $transaction['merchant']
        );

        if ($mappedName) {
            $category = $categories->first(
                fn (Category $category) =>
                    strcasecmp(
                        trim($category->name),
                        $mappedName
                    ) === 0
            );

            if ($category) {
                return $category;
            }
        }

        return null;
    }

    private function mapExternalCategory(string $category): ?string
    {
        return match (Str::lower(trim($category))) {
            'groceries' => 'Groceries',
            'gas' => 'Gas',
            'shopping' => 'Shopping',
            'gifts & donations' => 'Gifts & Donations',
            'miscellaneous' => 'Miscellaneous',

            'food & drink' => 'Dining',

            'bills & utilities' => 'Utilities',

            /*
             * Your default category is "House".
             */
            'home' => 'House',

            default => null,
        };
    }

    private function normalizeMerchant(string $description): string
    {
        $description = trim(
            preg_replace('/\s+/', ' ', $description)
        );

        $upper = Str::upper($description);

        /*
         * Start with known normalization rules.
         * We can add to this as we encounter real CSV data.
         */

        if (str_contains($upper, 'NEWREZ')) {
            return 'Newrez Mortgage';
        }

        if (str_contains($upper, 'SUMTER ELECTRIC')) {
            return 'Sumter Electric';
        }

        if (
            str_contains($upper, 'VZWRLSS')
            || str_contains($upper, 'VERIZON')
        ) {
            return 'Verizon';
        }

        if (str_contains($upper, 'AUTO-OWNERS')) {
            return 'Auto-Owners Insurance';
        }

        if (str_contains($upper, 'WSFS BANK')) {
            return 'WSFS Bank';
        }

        if (str_contains($upper, 'PROFIT OPTIMIZER')) {
            return 'Profit Optimizer';
        }

        /*
         * Credit card descriptions tend to already be usable.
         */
        return Str::title(
            Str::lower($description)
        );
    }

    private function merchantKey(?string $merchant): string
    {
        return Str::of($merchant ?? '')
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '')
            ->toString();
    }

    private function mapMerchantCategory(string $merchant): ?string
    {
        $merchant = Str::lower($merchant);

        return match (true) {
            str_contains($merchant, 'newrez') => 'House',

            str_contains($merchant, 'sumter electric') => 'Utilities',

            str_contains($merchant, 'vzwrlss'),
            str_contains($merchant, 'verizon') => 'Phone',

            str_contains($merchant, 'auto-owners') => 'Insurance',

            str_contains($merchant, 'wsfs bank') => 'Debt Payments',

            /*
             * These deposits are income, so use one of your
             * existing income categories if appropriate.
             */
            str_contains($merchant, 'profit optimizer') => 'Paycheck',

            default => null,
        };
    }

    private function isDuplicate(
        int $userId,
        array $transaction,
    ): bool {
        return Transaction::query()
            ->where('user_id', $userId)
            ->where('type', $transaction['type'])
            ->whereDate('due_at', $transaction['date'])
            ->where('amount', $transaction['amount'])
            ->whereRaw(
                'LOWER(merchant) = ?',
                [Str::lower($transaction['merchant'])]
            )
            ->exists();
    }
}
