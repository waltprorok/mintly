<?php

namespace App\Services;

use App\Models\CategoryBudget;
use App\Models\Transaction;
use Carbon\Carbon;

class CategoryBudgetService
{
    public function generate(
        int    $userId,
        Carbon $sourceMonth,
        Carbon $targetMonth
    ): int
    {
        $expenses = Transaction::query()
            ->where('user_id', $userId)
            ->where('type', 'expense')
            ->whereNotNull('category_id')
            ->whereBetween('due_at', [
                $sourceMonth->copy()->startOfMonth(),
                $sourceMonth->copy()->endOfMonth(),
            ])
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->get();

        $created = 0;

        foreach ($expenses as $expense) {
            $budget = CategoryBudget::query()->firstOrCreate(
                [
                    'user_id' => $userId,
                    'category_id' => $expense->category_id,
                    'month' => $targetMonth->copy()->startOfMonth()->toDateString(),
                ],
                [
                    'amount' => $expense->total,
                ]
            );

            if ($budget->wasRecentlyCreated) {
                $created++;
            }
        }

        return $created;
    }

    public function copyToNextMonth(
        int $userId,
        Carbon $sourceMonth,
        Carbon $targetMonth
    ): int
    {
        $budgets = CategoryBudget::query()
            ->where('user_id', $userId)
            ->whereDate('month', $sourceMonth->copy()->startOfMonth())
            ->get();

        $created = 0;

        foreach ($budgets as $budget) {
            $newBudget = CategoryBudget::query()->firstOrCreate(
                [
                    'user_id' => $userId,
                    'category_id' => $budget->category_id,
                    'month' => $targetMonth->copy()->startOfMonth()->toDateString(),
                ],
                [
                    'amount' => $budget->amount,
                ]
            );

            if ($newBudget->wasRecentlyCreated) {
                $created++;
            }
        }

        return $created;
    }
}
