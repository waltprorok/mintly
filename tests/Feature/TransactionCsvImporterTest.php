<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionCsvImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class TransactionCsvImporterTest extends TestCase
{
    use RefreshDatabase;

    private function importCsv(
        User   $user,
        string $csv,
        string $accountType = 'auto',
        string $amountConvention = 'negative_expense',
    ): array
    {
        Storage::fake('local');

        Storage::disk('local')->put(
            'transaction-imports/test.csv',
            $csv
        );

        return app(TransactionCsvImporter::class)->import(
            userId: $user->id,
            path: Storage::disk('local')->path('transaction-imports/test.csv'),
            accountType: $accountType,
            amountConvention: $amountConvention,
        );
    }

    public function test_it_imports_bank_transactions_and_skips_card_payments(): void
    {
        $user = User::factory()->create();

        $category = Category::factory()->create([
            'user_id' => $user->id,
            'name' => 'Utilities',
            'type' => 'expense',
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'merchant' => 'Sumter Electric',
            'type' => 'expense',
            'amount' => 200,
            'due_at' => '2026-08-01',
        ]);

        $csv = <<<'CSV'
            Details,Posting Date,Description,Amount,Type,Balance,Check or Slip #
            DEBIT,09/09/2026,Sumter Electric,-235.00,ACH_DEBIT,1200.00,
            DEBIT,09/18/2026,Payment to Chase card ending in 2612,-1305.05,ACH_DEBIT,500.00,
            CSV;

        $result = $this->importCsv($user, $csv);

        $this->assertSame(1, $result['created']);
        $this->assertSame(1, $result['transfers']);
        $this->assertSame(0, $result['duplicates']);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'amount' => 235.00,
            'type' => 'expense',
            'category_id' => $category->id,
        ]);
    }

    public function test_it_imports_chase_credit_card_transactions(): void
    {
        $user = User::factory()->create();

        $csv = <<<'CSV'
            Transaction Date,Post Date,Description,Category,Type,Amount,Memo
            10/01/2026,10/02/2026,NETFLIX,Shopping,Sale,-15.99,
            10/03/2026,10/04/2026,WALMART,Groceries,Sale,-125.50,
            10/05/2026,10/06/2026,PAYMENT THANK YOU,,Payment,500.00,
            CSV;

        $result = $this->importCsv($user, $csv);

        $this->assertSame(2, $result['created']);
        $this->assertSame(1, $result['transfers']);
        $this->assertSame(0, $result['duplicates']);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'merchant' => 'Netflix',
            'amount' => 15.99,
            'type' => 'expense',
            'payment_method' => 'credit_card',
        ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'merchant' => 'Walmart',
            'amount' => 125.50,
            'type' => 'expense',
            'payment_method' => 'credit_card',
        ]);

        $this->assertDatabaseMissing('transactions', [
            'user_id' => $user->id,
            'amount' => 500.00,
        ]);
    }

    public function test_it_imports_positive_expense_credit_card_transactions(): void
    {
        $user = User::factory()->create();

        $csv = <<<'CSV'
            Date,Description,Amount,Category
            10/01/2026,NETFLIX,15.99,Services
            10/02/2026,AMAZON,85.50,Merchandise
            CSV;

        $result = $this->importCsv(
            user: $user,
            csv: $csv,
            accountType: 'credit_card',
            amountConvention: 'positive_expense',
        );

        $this->assertSame(2, $result['created']);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'merchant' => 'Netflix',
            'amount' => 15.99,
            'type' => 'expense',
            'payment_method' => 'credit_card',
        ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'merchant' => 'Amazon',
            'amount' => 85.50,
            'type' => 'expense',
        ]);
    }

    public function test_it_imports_separate_debit_and_credit_columns(): void
    {
        $user = User::factory()->create();

        $csv = <<<'CSV'
            Date,Description,Debit,Credit
            10/01/2026,NETFLIX,15.99,
            10/02/2026,AMAZON,85.50,
            10/03/2026,PAYROLL,,2500.00
            CSV;

        $result = $this->importCsv(
            user: $user,
            csv: $csv,
            accountType: 'bank',
        );

        $this->assertSame(3, $result['created']);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'merchant' => 'Netflix',
            'amount' => 15.99,
            'type' => 'expense',
        ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'merchant' => 'Payroll',
            'amount' => 2500.00,
            'type' => 'income',
        ]);
    }

    public function test_it_imports_credit_card_refunds(): void
    {
        $user = User::factory()->create();

        $csv = <<<'CSV'
            Transaction Date,Description,Type,Amount
            10/01/2026,AMAZON,Sale,-85.50
            10/02/2026,AMAZON REFUND,Refund,25.00
            CSV;

        $result = $this->importCsv($user, $csv);

        $this->assertSame(2, $result['created']);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'amount' => 85.50,
            'type' => 'expense',
        ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'amount' => 25.00,
            'type' => 'income',
        ]);
    }

    public function test_it_skips_common_credit_card_payment_descriptions(): void
    {
        $user = User::factory()->create();

        $csv = <<<'CSV'
            Date,Description,Amount
            10/01/2026,AUTOMATIC PAYMENT - THANK YOU,-500.00
            10/02/2026,PAYMENT RECEIVED,-250.00
            10/03/2026,CREDIT CARD AUTOPAY,-100.00
            10/04/2026,NETFLIX,15.99
            CSV;

        $result = $this->importCsv(
            user: $user,
            csv: $csv,
            accountType: 'credit_card',
            amountConvention: 'positive_expense',
        );

        $this->assertSame(1, $result['created']);
        $this->assertSame(3, $result['transfers']);

        $this->assertDatabaseCount('transactions', 1);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'merchant' => 'Netflix',
            'type' => 'expense',
        ]);
    }

    public function test_it_skips_duplicate_transactions(): void
    {
        $user = User::factory()->create();

        $csv = <<<'CSV'
            Date,Description,Amount
            10/01/2026,NETFLIX,-15.99
            10/01/2026,NETFLIX,-15.99
            10/02/2026,AMAZON,-85.50
            CSV;

        $result = $this->importCsv($user, $csv);

        $this->assertSame(2, $result['created']);
        $this->assertSame(1, $result['duplicates']);

        $this->assertDatabaseCount('transactions', 2);
    }

    public function test_it_does_not_duplicate_transactions_when_importing_the_same_csv_twice(): void
    {
        $user = User::factory()->create();

        $csv = <<<'CSV'
            Date,Description,Amount
            10/01/2026,NETFLIX,-15.99
            10/02/2026,AMAZON,-85.50
            CSV;

        $first = $this->importCsv($user, $csv);
        $second = $this->importCsv($user, $csv);

        $this->assertSame(2, $first['created']);
        $this->assertSame(0, $first['duplicates']);

        $this->assertSame(0, $second['created']);
        $this->assertSame(2, $second['duplicates']);

        $this->assertDatabaseCount('transactions', 2);
    }

    public function test_it_rolls_back_all_transactions_when_an_import_fails(): void
    {
        $user = User::factory()->create();

        $csv = <<<'CSV'
            Date,Description,Amount
            10/01/2026,NETFLIX,-15.99
            10/02/2026,AMAZON,-85.50
            10/03/2026,WALMART,-125.00
            10/04/2026,TARGET,INVALID_AMOUNT
            CSV;

        try {
            $this->importCsv($user, $csv);

            $this->fail('Expected CSV import to throw an exception.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString(
                'Invalid transaction amount',
                $exception->getMessage()
            );
        }

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_it_preserves_existing_transactions_when_an_import_fails(): void
    {
        $user = User::factory()->create();

        $existing = Transaction::factory()->create([
            'user_id' => $user->id,
            'merchant' => 'Existing Transaction',
            'amount' => 100.00,
        ]);

        $csv = <<<'CSV'
            Date,Description,Amount
            10/01/2026,NETFLIX,-15.99
            10/02/2026,AMAZON,-85.50
            10/03/2026,WALMART,-125.00
            10/04/2026,TARGET,INVALID_AMOUNT
            CSV;

        try {
            $this->importCsv($user, $csv);

            $this->fail('Expected CSV import to throw an exception.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString(
                'Invalid transaction amount',
                $exception->getMessage()
            );
        }

        /*
         * Only the original transaction should remain.
         * All three newly imported transactions must be rolled back.
         */
        $this->assertDatabaseCount('transactions', 1);

        $this->assertDatabaseHas('transactions', [
            'id' => $existing->id,
            'user_id' => $user->id,
            'merchant' => 'Existing Transaction',
        ]);

        /*
         * Verify that none of the imported transactions were saved.
         */
        $this->assertDatabaseMissing('transactions', [
            'user_id' => $user->id,
            'merchant' => 'Netflix',
        ]);

        $this->assertDatabaseMissing('transactions', [
            'user_id' => $user->id,
            'merchant' => 'Amazon',
        ]);

        $this->assertDatabaseMissing('transactions', [
            'user_id' => $user->id,
            'merchant' => 'Walmart',
        ]);
    }

    public function test_it_prefers_amount_over_balance(): void
    {
        $user = User::factory()->create();

        $csv = <<<'CSV'
            Date,Description,Balance,Amount
            10/01/2026,NETFLIX,5000.00,-15.99
            10/02/2026,AMAZON,4914.50,-85.50
            CSV;

        $result = $this->importCsv($user, $csv);

        $this->assertSame(2, $result['created']);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'merchant' => 'Netflix',
            'amount' => 15.99,
        ]);

        $this->assertDatabaseMissing('transactions', [
            'user_id' => $user->id,
            'amount' => 5000.00,
        ]);
    }

    public function test_it_rejects_ambiguous_amount_columns(): void
    {
        $user = User::factory()->create();

        $csv = <<<'CSV'
            Date,Description,Value One,Value Two
            10/01/2026,NETFLIX,15.99,100.00
            10/02/2026,AMAZON,85.50,200.00
            CSV;

        $this->expectException(RuntimeException::class);

        $this->expectExceptionMessage(
            'Unable to determine which CSV column contains transaction amounts.'
        );

        $this->importCsv($user, $csv);
    }

    public function test_it_rejects_balance_only_csv_files(): void
    {
        $user = User::factory()->create();

        $csv = <<<'CSV'
            Date,Description,Balance
            10/01/2026,NETFLIX,5000.00
            10/02/2026,AMAZON,4914.50
            CSV;

        $this->expectException(RuntimeException::class);

        $this->expectExceptionMessage(
            'Unable to identify a transaction amount column.'
        );

        $this->importCsv($user, $csv);
    }

    public function test_it_handles_utf8_bom_headers(): void
    {
        $user = User::factory()->create();

        $csv = "\xEF\xBB\xBF" . <<<'CSV'
            Date,Description,Amount
            10/01/2026,NETFLIX,-15.99
            CSV;

        $result = $this->importCsv($user, $csv);

        $this->assertSame(1, $result['created']);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'merchant' => 'Netflix',
            'amount' => 15.99,
        ]);
    }

    public function test_it_rejects_duplicate_csv_headers(): void
    {
        $user = User::factory()->create();

        $csv = <<<'CSV'
            Date,Description,Amount,Amount
            10/01/2026,NETFLIX,-15.99,-15.99
            CSV;

        $this->expectException(RuntimeException::class);

        $this->expectExceptionMessage(
            'The CSV contains duplicate column headers.'
        );

        $this->importCsv($user, $csv);
    }

    public function test_it_rejects_empty_csv_files(): void
    {
        $user = User::factory()->create();

        $this->expectException(RuntimeException::class);

        $this->expectExceptionMessage(
            'The CSV file is empty.'
        );

        $this->importCsv($user, '');
    }

    public function test_it_rejects_invalid_account_types(): void
    {
        $user = User::factory()->create();

        $csv = <<<'CSV'
            Date,Description,Amount
            10/01/2026,NETFLIX,-15.99
            CSV;

        $this->expectException(RuntimeException::class);

        $this->expectExceptionMessage('Invalid CSV account type.');

        $this->importCsv(
            user: $user,
            csv: $csv,
            accountType: 'invalid',
        );
    }

    public function test_it_rejects_invalid_amount_conventions(): void
    {
        $user = User::factory()->create();

        $csv = <<<'CSV'
            Date,Description,Amount
            10/01/2026,NETFLIX,-15.99
            CSV;

        $this->expectException(RuntimeException::class);

        $this->expectExceptionMessage('Invalid CSV amount convention.');

        $this->importCsv(
            user: $user,
            csv: $csv,
            amountConvention: 'invalid',
        );
    }
}
