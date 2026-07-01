<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_filters_income_and_expense_categories()
    {
        Category::factory()->create(['type' => 'income']);
        Category::factory()->create(['type' => 'expense']);

        $this->assertCount(1, Category::income()->get());
        $this->assertCount(1, Category::expense()->get());
    }

    public function test_it_filters_discretionary_and_non_discretionary_categories()
    {
        Category::factory()->create(['spend_classification' => 'discretionary']);
        Category::factory()->create(['spend_classification' => 'non_discretionary']);

        $this->assertCount(1, Category::discretionary()->get());
        $this->assertCount(1, Category::nonDiscretionary()->get());
    }

    public function test_it_belongs_to_user()
    {
        $user = User::factory()->create();

        $category = Category::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertTrue($category->user->is($user));
    }

    public function test_it_has_many_transactions()
    {
        $category = Category::factory()->create();

        $transactions = Transaction::factory()->count(3)->create([
            'category_id' => $category->id,
        ]);

        $this->assertCount(3, $category->transactions);
        $this->assertTrue($category->transactions->first()->is($transactions->first()));
    }
}
