<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class MonthlyExpenses extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    protected function getColumns(): int | array
    {
        return [
            'default' => 1,
            'lg' => 1,
        ];
    }

    protected function getStats(): array
    {
        $userId = auth()->id();
        $now = Carbon::now();

        // Current month expenses
        $currentMonthExpenses = Transaction::query()
            ->where('user_id', $userId)
            ->where('type', 'expense')
            ->whereMonth('due_at', $now->month)
            ->whereYear('due_at', $now->year)
            ->sum('amount');

        // Previous month expenses
        $previousMonth = $now->copy()->subMonth();

        $previousMonthExpenses = Transaction::query()
            ->where('user_id', $userId)
            ->where('type', 'expense')
            ->whereMonth('due_at', $previousMonth->month)
            ->whereYear('due_at', $previousMonth->year)
            ->sum('amount');

        // Percentage change
        $percentChange = $previousMonthExpenses > 0
            ? (($currentMonthExpenses - $previousMonthExpenses) / $previousMonthExpenses) * 100
            : null;

        return [
            Stat::make('Monthly Expenses', '$' . number_format($currentMonthExpenses, 2))
                ->description(
                    $percentChange !== null
                        ? number_format($percentChange, 1) . '% from last month'
                        : 'No previous data'
                )
                ->color('danger') // red
                ->icon('heroicon-o-arrow-trending-down'),
        ];
    }
}
