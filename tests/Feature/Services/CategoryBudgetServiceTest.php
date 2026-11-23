<?php

namespace Tests\Unit\Services;

use App\Models\Category;
use App\Models\CategoryBudget;
use App\Models\Transaction;
use App\Models\User;
use App\Services\CategoryBudgetService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryBudgetServiceTest extends TestCase
{
    use RefreshDatabase;

    private CategoryBudgetService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new CategoryBudgetService();
    }

    public function test_generate_creates_budget_from_expenses(): void
    {
        $user = User::factory()->create();

        $category = Category::factory()->create([
            'user_id' => $user->id,
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 150.00,
            'due_at' => '2026-10-10 00:00:00',
        ]);

        $created = $this->service->generate(
            $user->id,
            Carbon::parse('2026-10-01'),
            Carbon::parse('2026-11-01')
        );

        $this->assertEquals(1, $created);

        $this->assertDatabaseHas('category_budgets', [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'month' => '2026-11-01',
            'amount' => 150.00,
        ]);
    }

    public function test_generate_sums_expenses_by_category(): void
    {
        $user = User::factory()->create();

        $category = Category::factory()->create([
            'user_id' => $user->id,
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 100.00,
            'due_at' => '2026-10-05 00:00:00',
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 250.00,
            'due_at' => '2026-10-15 00:00:00',
        ]);

        $created = $this->service->generate(
            $user->id,
            Carbon::parse('2026-10-01'),
            Carbon::parse('2026-11-01')
        );

        $this->assertEquals(1, $created);

        $this->assertDatabaseHas('category_budgets', [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'month' => '2026-11-01',
            'amount' => 350.00,
        ]);
    }

    public function test_generate_creates_separate_budgets_for_each_category(): void
    {
        $user = User::factory()->create();

        $category1 = Category::factory()->create([
            'user_id' => $user->id,
        ]);

        $category2 = Category::factory()->create([
            'user_id' => $user->id,
        ]);

        foreach ([
                     [$category1->id, 200.00],
                     [$category2->id, 300.00],
                 ] as [$categoryId, $amount]) {
            Transaction::factory()->create([
                'user_id' => $user->id,
                'category_id' => $categoryId,
                'type' => 'expense',
                'amount' => $amount,
                'due_at' => '2026-10-10',
            ]);
        }

        $created = $this->service->generate(
            $user->id,
            Carbon::parse('2026-10-01'),
            Carbon::parse('2026-11-01')
        );

        $this->assertEquals(2, $created);

        $this->assertDatabaseCount('category_budgets', 2);
    }

    public function test_generate_ignores_income_transactions(): void
    {
        $user = User::factory()->create();

        $category = Category::factory()->create([
            'user_id' => $user->id,
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'type' => 'income',
            'amount' => 1000.00,
            'due_at' => '2026-10-10',
        ]);

        $created = $this->service->generate(
            $user->id,
            Carbon::parse('2026-10-01'),
            Carbon::parse('2026-11-01')
        );

        $this->assertEquals(0, $created);

        $this->assertDatabaseCount('category_budgets', 0);
    }

    public function test_generate_ignores_expenses_outside_source_month(): void
    {
        $user = User::factory()->create();

        $category = Category::factory()->create([
            'user_id' => $user->id,
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 100.00,
            'due_at' => '2026-09-15',
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 200.00,
            'due_at' => '2026-11-15',
        ]);

        $created = $this->service->generate(
            $user->id,
            Carbon::parse('2026-10-01'),
            Carbon::parse('2026-11-01')
        );

        $this->assertEquals(0, $created);

        $this->assertDatabaseCount('category_budgets', 0);
    }

    public function test_generate_ignores_transactions_without_category(): void
    {
        $user = User::factory()->create();

        Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => null,
            'type' => 'expense',
            'amount' => 100.00,
            'due_at' => '2026-10-10',
        ]);

        $created = $this->service->generate(
            $user->id,
            Carbon::parse('2026-10-01'),
            Carbon::parse('2026-11-01')
        );

        $this->assertEquals(0, $created);

        $this->assertDatabaseCount('category_budgets', 0);
    }

    public function test_generate_does_not_duplicate_existing_budget(): void
    {
        $user = User::factory()->create();

        $category = Category::factory()->create([
            'user_id' => $user->id,
        ]);

        CategoryBudget::query()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'month' => '2026-11-01',
            'amount' => 500.00,
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 200.00,
            'due_at' => '2026-10-10',
        ]);

        $created = $this->service->generate(
            $user->id,
            Carbon::parse('2026-10-01'),
            Carbon::parse('2026-11-01')
        );

        $this->assertEquals(0, $created);

        $this->assertDatabaseHas('category_budgets', [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'month' => '2026-11-01',
            'amount' => 500.00,
        ]);

        $this->assertDatabaseCount('category_budgets', 1);
    }

    public function test_generate_only_uses_transactions_for_specified_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $category = Category::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        Transaction::factory()->create([
            'user_id' => $otherUser->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 300.00,
            'due_at' => '2026-10-10',
        ]);

        $created = $this->service->generate(
            $user->id,
            Carbon::parse('2026-10-01'),
            Carbon::parse('2026-11-01')
        );

        $this->assertEquals(0, $created);

        $this->assertDatabaseCount('category_budgets', 0);
    }

    public function test_copy_to_next_month_creates_budget(): void
    {
        $user = User::factory()->create();

        $category = Category::factory()->create([
            'user_id' => $user->id,
        ]);

        CategoryBudget::query()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'month' => '2026-10-01',
            'amount' => 450.00,
        ]);

        $created = $this->service->copyToNextMonth(
            $user->id,
            Carbon::parse('2026-10-01'),
            Carbon::parse('2026-11-01')
        );

        $this->assertEquals(1, $created);

        $this->assertDatabaseHas('category_budgets', [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'month' => '2026-11-01',
            'amount' => 450.00,
        ]);
    }

    public function test_copy_to_next_month_copies_multiple_budgets(): void
    {
        $user = User::factory()->create();

        $category1 = Category::factory()->create([
            'user_id' => $user->id,
        ]);

        $category2 = Category::factory()->create([
            'user_id' => $user->id,
        ]);

        foreach ([
                     [$category1->id, 200.00],
                     [$category2->id, 350.00],
                 ] as [$categoryId, $amount]) {
            CategoryBudget::query()->create([
                'user_id' => $user->id,
                'category_id' => $categoryId,
                'month' => '2026-10-01',
                'amount' => $amount,
            ]);
        }

        $created = $this->service->copyToNextMonth(
            $user->id,
            Carbon::parse('2026-10-01'),
            Carbon::parse('2026-11-01')
        );

        $this->assertEquals(2, $created);

        $this->assertDatabaseCount('category_budgets', 4);
    }

    public function test_copy_to_next_month_does_not_duplicate_existing_budget(): void
    {
        $user = User::factory()->create();

        $category = Category::factory()->create([
            'user_id' => $user->id,
        ]);

        CategoryBudget::query()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'month' => '2026-10-01',
            'amount' => 300.00,
        ]);

        CategoryBudget::query()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'month' => '2026-11-01',
            'amount' => 500.00,
        ]);

        $created = $this->service->copyToNextMonth(
            $user->id,
            Carbon::parse('2026-10-01'),
            Carbon::parse('2026-11-01')
        );

        $this->assertEquals(0, $created);

        $this->assertDatabaseHas('category_budgets', [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'month' => '2026-11-01',
            'amount' => 500.00,
        ]);

        $this->assertDatabaseCount('category_budgets', 2);
    }

    public function test_copy_to_next_month_only_copies_specified_users_budgets(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $category = Category::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        CategoryBudget::query()->create([
            'user_id' => $otherUser->id,
            'category_id' => $category->id,
            'month' => '2026-10-01',
            'amount' => 400.00,
        ]);

        $created = $this->service->copyToNextMonth(
            $user->id,
            Carbon::parse('2026-10-01'),
            Carbon::parse('2026-11-01')
        );

        $this->assertEquals(0, $created);

        $this->assertDatabaseCount('category_budgets', 1);
    }

    public function test_copy_to_next_month_returns_zero_when_no_budgets_exist(): void
    {
        $user = User::factory()->create();

        $created = $this->service->copyToNextMonth(
            $user->id,
            Carbon::parse('2026-10-01'),
            Carbon::parse('2026-11-01')
        );

        $this->assertEquals(0, $created);

        $this->assertDatabaseCount('category_budgets', 0);
    }
}
