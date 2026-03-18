<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BudgetStats extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = null;

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

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        $month = $this->month;
        $year = $this->year;

        $userId = auth()->id();

        $current = Carbon::create($year, $month);
        $previous = $current->copy()->subMonth();

        $income = Transaction::query()
            ->where('user_id', $userId)
            ->where('type', 'income')
            ->whereMonth('due_at', $month)
            ->whereYear('due_at', $year)
            ->sum('amount');

        $expenses = Transaction::query()
            ->where('user_id', $userId)
            ->where('type', 'expense')
            ->whereMonth('due_at', $month)
            ->whereYear('due_at', $year)
            ->sum('amount');

        $net = $income - $expenses;

        $prevIncome = Transaction::query()
            ->where('user_id', $userId)
            ->where('type', 'income')
            ->whereMonth('due_at', $previous->month)
            ->whereYear('due_at', $previous->year)
            ->sum('amount');

        $prevExpenses = Transaction::query()
            ->where('user_id', $userId)
            ->where('type', 'expense')
            ->whereMonth('due_at', $previous->month)
            ->whereYear('due_at', $previous->year)
            ->sum('amount');

        $prevNet = $prevIncome - $prevExpenses;

        $paidExpenses = Transaction::query()
            ->where('user_id', $userId)
            ->where('type', 'expense')
            ->where('status', true)
            ->whereMonth('due_at', $month)
            ->whereYear('due_at', $year)
            ->sum('amount');

        $unpaidExpenses = Transaction::query()
            ->where('user_id', $userId)
            ->where('type', 'expense')
            ->where('status', false)
            ->whereMonth('due_at', $month)
            ->whereYear('due_at', $year)
            ->sum('amount');

        $paidPercent = $expenses > 0
            ? round(($paidExpenses / $expenses) * 100)
            : 0;

        $unpaidPercent = $expenses > 0
            ? round(($unpaidExpenses / $expenses) * 100)
            : 0;

        $incomeChange = $this->percentChange($income, $prevIncome);
        $expenseChange = $this->percentChange($expenses, $prevExpenses);
        $netChange = $this->percentChange($net, $prevNet);

        return [
            Stat::make('Monthly Income', '$' . number_format($income, 2))
                ->description($incomeChange)
                ->descriptionIcon($this->trendIcon($incomeChange))
                ->color('success'),

            Stat::make('Monthly Expenses', '$' . number_format($expenses, 2))
                ->description($expenseChange)
                ->descriptionIcon($this->trendIcon($expenseChange))
                ->color('danger'),

            Stat::make('Net This Month', '$' . number_format($net, 2))
                ->description($netChange)
                ->descriptionIcon($this->trendIcon($netChange))
                ->color($net >= 0 ? 'success' : 'danger'),

            Stat::make('Bills Paid', '$' . number_format($paidExpenses, 2))
                ->description($paidPercent . '% of expenses cleared')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Outstanding Bills', '$' . number_format($unpaidExpenses, 2))
                ->description($unpaidPercent . '% remaining')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Expense Progress', $paidPercent . '%')
                ->description('of monthly expenses paid')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($paidPercent > 75 ? 'success' : 'warning'),
        ];
    }

    private function percentChange($current, $previous): string
    {
        if ($previous == 0) {
            return 'No previous data';
        }

        $percent = (($current - $previous) / $previous) * 100;

        return number_format($percent, 1) . '% from last month';
    }

    private function trendIcon($change): string
    {
        if (str_contains($change, '-')) {
            return 'heroicon-m-arrow-trending-down';
        }

        return 'heroicon-m-arrow-trending-up';
    }
}
