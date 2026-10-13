<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionCsvImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TransactionCsvImporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_bank_transactions_and_skips_card_payments(): void
    {
        Storage::fake('local');

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

        Storage::disk('local')->put(
            'transaction-imports/test.csv',
            $csv
        );

        $result = app(TransactionCsvImporter::class)->import(
            userId: $user->id,
            path: Storage::disk('local')->path(
                'transaction-imports/test.csv'
            ),
        );

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
}
