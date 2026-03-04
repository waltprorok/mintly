<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class MonthlyNet extends StatsOverviewWidget
{
    protected static ?int $sort = 3;

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

        // Current month totals
        $currentIncome = Transaction::query()
            ->where('user_id', $userId)
            ->where('type', 'income')
            ->whereMonth('due_at', $now->month)
            ->whereYear('due_at', $now->year)
            ->sum('amount');

        $currentExpenses = Transaction::query()
            ->where('user_id', $userId)
            ->where('type', 'expense')
            ->whereMonth('due_at', $now->month)
            ->whereYear('due_at', $now->year)
            ->sum('amount');

        $currentNet = $currentIncome - $currentExpenses;

        // Previous month
        $previousMonth = $now->copy()->subMonth();

        $previousIncome = Transaction::query()
            ->where('user_id', $userId)
            ->where('type', 'income')
            ->whereMonth('due_at', $previousMonth->month)
            ->whereYear('due_at', $previousMonth->year)
            ->sum('amount');

        $previousExpenses = Transaction::query()
            ->where('user_id', $userId)
            ->where('type', 'expense')
            ->whereMonth('due_at', $previousMonth->month)
            ->whereYear('due_at', $previousMonth->year)
            ->sum('amount');

        $previousNet = $previousIncome - $previousExpenses;

        $percentChange = $previousNet != 0
            ? (($currentNet - $previousNet) / abs($previousNet)) * 100
            : null;

        return [
            Stat::make('Net This Month', '$' . number_format($currentNet, 2))
                ->description(
                    $percentChange !== null
                        ? number_format($percentChange, 1) . '% from last month'
                        : 'No previous data'
                )
                ->color($currentNet >= 0 ? 'success' : 'danger')
                ->icon($currentNet >= 0
                    ? 'heroicon-o-arrow-trending-up'
                    : 'heroicon-o-arrow-trending-down'
                ),
        ];
    }
}
