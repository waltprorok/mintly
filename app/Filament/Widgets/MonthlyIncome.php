<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MonthlyIncome extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

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

        // Current month income
        $currentMonthIncome = Transaction::query()
            ->where('user_id', $userId)
            ->where('type', 'income')
            ->whereMonth('due_at', $now->month)
            ->whereYear('due_at', $now->year)
            ->sum('amount');

        // Previous month income
        $previousMonth = $now->copy()->subMonth();

        $previousMonthIncome = Transaction::query()
            ->where('user_id', $userId)
            ->where('type', 'income')
            ->whereMonth('due_at', $previousMonth->month)
            ->whereYear('due_at', $previousMonth->year)
            ->sum('amount');

        // Percentage change
        $percentChange = $previousMonthIncome > 0
            ? (($currentMonthIncome - $previousMonthIncome) / $previousMonthIncome) * 100
            : null;

        return [
            Stat::make('Monthly Income', '$' . number_format($currentMonthIncome, 2))
                ->description(
                    $percentChange !== null
                        ? number_format($percentChange, 1) . '% from last month'
                        : 'No previous data'
                )
                ->color('success')
                ->icon('heroicon-o-arrow-trending-up'),
        ];
    }
}
