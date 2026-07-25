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

        Carbon::setTestNow(Carbon::create(2026, 4, 10));

        $this->service = new RecurringTransactionService();
    }

    public function test_it_creates_next_month_transaction_for_monthly_rule(): void
    {
        $user = User::factory()->create();

        $category = Category::factory()->create([
            'user_id' => $user->id,
        ]);

        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'recurring_rule' => 'monthly',
            'due_at' => Carbon::create(2026, 4, 10),
            'amount' => 100,
        ]);

        $created = $this->service->run(
            userId: $user->id,
            nextMonthOnly: true,
            referenceDate: Carbon::create(2026, 4, 1),
        );

        $this->assertEquals(1, $created);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'merchant' => $transaction->merchant,
            'amount' => 100,
            'due_at' => '2026-05-10 00:00:00',
        ]);
    }

    public function test_it_does_not_duplicate_existing_transaction_in_same_week(): void
    {
        $user = User::factory()->create();

        $category = Category::factory()->create([
            'user_id' => $user->id,
        ]);

        $original = Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'recurring_rule' => 'monthly',
            'due_at' => Carbon::create(2026, 4, 5),
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'merchant' => $original->merchant,
            'type' => $original->type,
            'due_at' => Carbon::create(2026, 5, 7),
        ]);

        $created = $this->service->run(
            userId: $user->id,
            nextMonthOnly: true,
            referenceDate: Carbon::create(2026, 4, 1),
        );

        $this->assertEquals(0, $created);
    }

    public function test_it_does_not_modify_existing_is_paid_status(): void
    {
        $user = User::factory()->create();

        $category = Category::factory()->create([
            'user_id' => $user->id,
        ]);

        $source = Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'recurring_rule' => 'monthly',
            'due_at' => Carbon::create(2026, 4, 10),
        ]);

        $existing = Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'merchant' => $source->merchant,
            'type' => $source->type,
            'due_at' => Carbon::create(2026, 5, 10),
            'is_paid' => true,
        ]);

        $this->service->run(
            userId: $user->id,
            nextMonthOnly: true,
            referenceDate: Carbon::create(2026, 4, 1),
        );

        $this->assertDatabaseHas('transactions', [
            'id' => $existing->id,
            'is_paid' => true,
        ]);
    }

    public function test_it_does_not_use_transactions_outside_the_source_month(): void
    {
        $user = User::factory()->create();

        $category = Category::factory()->create([
            'user_id' => $user->id,
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'recurring_rule' => 'monthly',
            'due_at' => Carbon::create(2026, 3, 10),
        ]);

        $created = $this->service->run(
            userId: $user->id,
            nextMonthOnly: true,
            referenceDate: Carbon::create(2026, 4, 1),
        );

        $this->assertEquals(0, $created);

        $this->assertDatabaseMissing('transactions', [
            'user_id' => $user->id,
            'due_at' => '2026-05-10 00:00:00',
        ]);
    }

    public function test_it_handles_biweekly_transactions_correctly(): void
    {
        $user = User::factory()->create();

        $category = Category::factory()->create([
            'user_id' => $user->id,
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'recurring_rule' => 'biweekly',
            'due_at' => Carbon::create(2026, 4, 20),
        ]);

        $created = $this->service->run(
            userId: $user->id,
            nextMonthOnly: true,
            referenceDate: Carbon::create(2026, 4, 1),
        );

        $this->assertEquals(2, $created);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'due_at' => '2026-05-04 00:00:00',
        ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'due_at' => '2026-05-18 00:00:00',
        ]);
    }
}
