<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\RecurringTransactionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecurringTransactionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected RecurringTransactionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create(2026, 4, 10)); // freeze time
        $this->service = new RecurringTransactionService();
    }

    public function test_it_creates_next_month_transaction_for_monthly_rule()
    {
        $user = User::factory()->create();

        $category = Category::factory()->create([
            'user_id' => $user->id,
        ]);

        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'recurring_rule' => 'monthly',
            'due_at' => now()->subMonth(), // March 10
            'amount' => 100,
        ]);

        $created = $this->service->run($user->id, true);

        $this->assertEquals(1, $created);

        $expectedDate = now()
            ->copy()
            ->addMonth()
            ->startOfMonth()
            ->addDays(9)
            ->format('Y-m-d H:i:s'); // ✅ SQLite-safe

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'merchant' => $transaction->merchant,
            'amount' => 100,
            'due_at' => $expectedDate,
        ]);
    }

    public function test_it_does_not_duplicate_existing_transaction_in_same_week()
    {
        $user = User::factory()->create();

        $category = Category::factory()->create([
            'user_id' => $user->id,
        ]);

        $original = Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'recurring_rule' => 'monthly',
            'due_at' => now()->subMonth()->startOfMonth()->addDays(4), // March 5
        ]);

        $nextDate = now()->copy()->addMonth()->startOfMonth()->addDays(6); // May 7

        Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'merchant' => $original->merchant,
            'type' => $original->type,
            'due_at' => $nextDate,
        ]);

        $created = $this->service->run($user->id, true);

        $this->assertEquals(0, $created);
    }

    public function test_it_does_not_modify_existing_is_paid_status()
    {
        $user = User::factory()->create();

        $category = Category::factory()->create([
            'user_id' => $user->id,
        ]);

        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'recurring_rule' => 'monthly',
            'due_at' => now()->subMonth(),
            'is_paid' => true,
        ]);

        $this->service->run($user->id, false);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'is_paid' => true,
        ]);
    }

    public function test_it_skips_future_transactions_when_not_next_month_mode()
    {
        $user = User::factory()->create();

        $category = Category::factory()->create([
            'user_id' => $user->id,
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'recurring_rule' => 'monthly',
            'due_at' => now()->addMonth(), // future
        ]);

        $created = $this->service->run($user->id, false);

        $this->assertEquals(0, $created);
    }

    public function test_it_handles_biweekly_transactions_correctly()
    {
        $user = User::factory()->create();

        $category = Category::factory()->create([
            'user_id' => $user->id,
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'recurring_rule' => 'biweekly',
            'due_at' => now()->subWeeks(2),
        ]);

        $created = $this->service->run($user->id, true);

        $this->assertGreaterThanOrEqual(1, $created);
    }
}
