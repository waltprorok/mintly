<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use App\Models\CategoryBudget;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryBudgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes(): void
    {
        $model = new CategoryBudget();

        $this->assertEquals([
            'user_id',
            'category_id',
            'month',
            'amount',
        ], $model->getFillable());
    }

    public function test_month_is_cast_to_date(): void
    {
        $model = new CategoryBudget([
            'month' => '2026-11-01',
        ]);

        $this->assertInstanceOf(
            \Illuminate\Support\Carbon::class,
            $model->month
        );

        $this->assertEquals(
            '2026-11-01',
            $model->month->format('Y-m-d')
        );
    }

    public function test_amount_is_cast_to_decimal(): void
    {
        $model = new CategoryBudget([
            'amount' => 150.5,
        ]);

        $this->assertEquals('150.50', $model->amount);
    }

    public function test_user_relationship(): void
    {
        $model = new CategoryBudget();

        $this->assertInstanceOf(
            BelongsTo::class,
            $model->user()
        );

        $this->assertInstanceOf(
            User::class,
            $model->user()->getRelated()
        );

        $this->assertEquals(
            'user_id',
            $model->user()->getForeignKeyName()
        );
    }

    public function test_category_relationship(): void
    {
        $model = new CategoryBudget();

        $this->assertInstanceOf(
            BelongsTo::class,
            $model->category()
        );

        $this->assertInstanceOf(
            Category::class,
            $model->category()->getRelated()
        );

        $this->assertEquals(
            'category_id',
            $model->category()->getForeignKeyName()
        );
    }
}
