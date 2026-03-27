<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => 'expense',
            'category_id' => Category::factory(),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'due_at' => now(),
            'description' => $this->faker->sentence(),
            'notes' => null,
            'merchant' => $this->faker->company(),
            'payment_method' => 'bank',
            'is_recurring' => true,
            'recurring_rule' => 'monthly',
            'is_paid' => false,
        ];
    }

    public function income(): static
    {
        return $this->state(fn() => [
            'type' => 'income',
        ]);
    }

    public function expense(): static
    {
        return $this->state(fn() => [
            'type' => 'expense',
        ]);
    }

    public function monthly(): static
    {
        return $this->state(fn() => [
            'recurring_rule' => 'monthly',
        ]);
    }

    public function weekly(): static
    {
        return $this->state(fn() => [
            'recurring_rule' => 'weekly',
        ]);
    }

    public function biweekly(): static
    {
        return $this->state(fn() => [
            'recurring_rule' => 'biweekly',
        ]);
    }
}
