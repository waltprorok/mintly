<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WeeklyCashFlowStats extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    protected $listeners = ['updateBudgetStats'];

    public int $month;
    public int $year;

    public function mount(): void
    {
        $this->month = now()->month;
        $this->year = now()->year;
    }

    protected function getColumns(): int
    {
        return $this->getWeeksInMonth();
    }

    protected function getWeeksInMonth(): int
    {
        return (int)ceil(
            Carbon::create($this->year, $this->month)->daysInMonth / 7
        );
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

        $currentWeek = null;

        if (
            $this->month === now()->month &&
            $this->year === now()->year
        ) {
            $currentWeek = (int) ceil(now()->day / 7);
        }

        return collect(range(1, $this->getWeeksInMonth()))
            ->map(function ($week) use ($weeks, $currentWeek) {

                $income = $weeks[$week]->income ?? 0;
                $expenses = $weeks[$week]->expenses ?? 0;
                $net = $income - $expenses;

                $isCurrentWeek = $week === $currentWeek;

                return Stat::make(
                    $isCurrentWeek
                        ? "Week {$week} • Current"
                        : "Week {$week}",
                    '$' . number_format($net, 2)
                )
                    ->description(
                        '+$' . number_format($income, 2) . ' / -$' . number_format($expenses, 2)
                    )
                    ->icon(
                        $isCurrentWeek
                            ? 'heroicon-m-bolt'
                            : null
                    )
                    ->color($net >= 0 ? 'success' : 'danger');
            })
            ->toArray();
    }
}
