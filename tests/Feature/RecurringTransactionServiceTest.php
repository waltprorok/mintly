<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\RecurringTransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecurringTransactionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected RecurringTransactionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new RecurringTransactionService();
    }

    /** @test */
    public function it_creates_next_month_transaction_for_monthly_rule()
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
            'amount' => 100,
        ]);

        $created = $this->service->run($user->id, true);

        $this->assertEquals(1, $created);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'merchant' => $transaction->merchant,
            'amount' => 100,
        ]);
    }

    /** @test */
    public function it_does_not_duplicate_existing_transaction()
    {
        $user = User::factory()->create();

        $category = Category::factory()->create([
            'user_id' => $user->id,
        ]);

        $original = Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'recurring_rule' => 'monthly',
            'due_at' => now()->subMonth(),
        ]);

        $nextDate = now()
            ->copy()
            ->addMonth()
            ->setDay($original->due_at->day);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'merchant' => $original->merchant,
            'type' => $original->type,
            'recurring_rule' => 'monthly',
            'due_at' => $nextDate,
        ]);

        $created = $this->service->run($user->id, true);

        $this->assertEquals(0, $created);
    }

    /** @test */
    public function it_does_not_modify_existing_is_paid_status()
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
            'is_paid' => true, // ✅ should NOT be reset
        ]);
    }

    /** @test */
    public function it_skips_future_transactions()
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

    /** @test */
    public function it_handles_biweekly_transactions_correctly()
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
