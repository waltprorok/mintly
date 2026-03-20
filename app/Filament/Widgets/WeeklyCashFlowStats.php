<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WeeklyCashFlowStats extends StatsOverviewWidget
{
    protected static bool $isCollapsible = true;

    protected static bool $isCollapsed = true;

    protected static bool $isDiscovered = false;

    protected $listeners = ['updateBudgetStats'];

    public int $month;
    public int $year;

    public function mount(): void
    {
        $this->month = now()->month;
        $this->year = now()->year;
    }

    public function updateBudgetStats($month, $year): void
    {
        $this->month = $month;
        $this->year = $year;
    }

    protected function getStats(): array
    {
        $weeks = Transaction::query()
            ->where('user_id', auth()->id())
            ->whereMonth('due_at', $this->month)
            ->whereYear('due_at', $this->year)
            ->selectRaw("
                ((DAY(due_at) - 1) DIV 7) + 1 as week,
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expenses
            ")
            ->groupBy('week')
            ->get()
            ->keyBy('week');

        return collect(range(1, 4))->map(function ($week) use ($weeks) {

            $income = $weeks[$week]->income ?? 0;
            $expenses = $weeks[$week]->expenses ?? 0;
            $net = $income - $expenses;

            return Stat::make("Week {$week}", '$' . number_format($net, 2))
                ->description(
                    '+$' . number_format($income, 2) . ' / -$' . number_format($expenses, 2)
                )
                ->color($net >= 0 ? 'success' : 'danger');

        })->toArray();
    }
}
