<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_filters_income_transactions()
    {
        Transaction::factory()->create(['type' => 'income']);
        Transaction::factory()->create(['type' => 'expense']);

        $this->assertCount(1, Transaction::income()->get());
    }

    public function test_it_filters_expense_transactions()
    {
        Transaction::factory()->create(['type' => 'income']);
        Transaction::factory()->create(['type' => 'expense']);

        $this->assertCount(1, Transaction::expense()->get());
    }

    public function test_it_filters_paid_and_unpaid_transactions()
    {
        Transaction::factory()->create(['is_paid' => true]);
        Transaction::factory()->create(['is_paid' => false]);

        $this->assertCount(1, Transaction::paid()->get());
        $this->assertCount(1, Transaction::unpaid()->get());
    }

    public function test_it_identifies_income_and_expense_helpers()
    {
        $income = Transaction::factory()->create(['type' => 'income']);
        $expense = Transaction::factory()->create(['type' => 'expense']);

        $this->assertTrue($income->isIncome());
        $this->assertFalse($income->isExpense());

        $this->assertTrue($expense->isExpense());
        $this->assertFalse($expense->isIncome());
    }

    public function test_it_casts_attributes_correctly()
    {
        $transaction = Transaction::factory()->create([
            'amount' => 123.45,
            'due_at' => now(),
            'is_paid' => true,
        ]);

        $this->assertIsString((string) $transaction->amount);
        $this->assertInstanceOf(Carbon::class, $transaction->due_at);
        $this->assertIsBool($transaction->is_paid);
    }

    public function test_it_belongs_to_user_and_category()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);

        $this->assertTrue($transaction->user->is($user));
        $this->assertTrue($transaction->category->is($category));
    }
}
