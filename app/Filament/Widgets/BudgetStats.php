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

        // Current values
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

        // Previous values
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

        // Paid vs unpaid
        $paidExpenses = Transaction::query()
            ->where('user_id', $userId)
            ->where('type', 'expense')
            ->where('is_paid', true)
            ->whereMonth('due_at', $month)
            ->whereYear('due_at', $year)
            ->sum('amount');

        $unpaidExpenses = Transaction::query()
            ->where('user_id', $userId)
            ->where('type', 'expense')
            ->where('is_paid', false)
            ->whereMonth('due_at', $month)
            ->whereYear('due_at', $year)
            ->sum('amount');

        $paidPercent = $expenses > 0
            ? round(($paidExpenses / $expenses) * 100)
            : 0;

        $unpaidPercent = $expenses > 0
            ? round(($unpaidExpenses / $expenses) * 100)
            : 0;

        // Trend values
        $incomeChangeValue = $this->percentChangeValue($income, $prevIncome);
        $expenseChangeValue = $this->percentChangeValue($expenses, $prevExpenses);

        // Formatted descriptions
        $incomeChange = $this->formatPercentChange($incomeChangeValue);
        $expenseChange = $this->formatPercentChange($expenseChangeValue);
        $netChange = $this->formatNetChange($net, $prevNet);

        return [
            // Income (up = good)
            Stat::make('Monthly Income', '$' . number_format($income, 2))
                ->description($incomeChange)
                ->descriptionIcon($incomeChangeValue < 0
                    ? 'heroicon-m-arrow-trending-down'
                    : 'heroicon-m-arrow-trending-up'
                )
                ->color($incomeChangeValue < 0 ? 'danger' : 'success'),

            // Expenses (down = good)
            Stat::make('Monthly Expenses', '$' . number_format($expenses, 2))
                ->description($expenseChange)
                ->descriptionIcon($expenseChangeValue > 0
                    ? 'heroicon-m-arrow-trending-up'
                    : 'heroicon-m-arrow-trending-down'
                )
                ->color($expenseChangeValue > 0 ? 'danger' : 'success'),

            // Net (use dollar change instead of %)
            Stat::make('Net This Month', '$' . number_format($net, 2))
                ->description($netChange)
                ->descriptionIcon(($net - $prevNet) < 0
                    ? 'heroicon-m-arrow-trending-down'
                    : 'heroicon-m-arrow-trending-up'
                )
                ->color(($net - $prevNet) < 0 ? 'danger' : 'success'),

            // Paid bills
            Stat::make('Bills Paid', '$' . number_format($paidExpenses, 2))
                ->description($paidPercent . '% of expenses cleared')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            // Outstanding
            Stat::make('Outstanding Bills', '$' . number_format($unpaidExpenses, 2))
                ->description($unpaidPercent . '% remaining')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            // Progress
            Stat::make('Expense Progress', $paidPercent . '%')
                ->description('of monthly expenses paid')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($paidPercent > 75 ? 'success' : 'warning'),
        ];
    }

    private function percentChangeValue($current, $previous): float
    {
        if ($previous == 0) {
            return 0;
        }

        return (($current - $previous) / $previous) * 100;
    }

    private function formatPercentChange(float $value): string
    {
        return number_format($value, 1) . '% from last month';
    }

    private function formatNetChange($current, $previous): string
    {
        $diff = $current - $previous;

        $prefix = $diff < 0 ? '-' : '+';

        return $prefix . '$' . number_format(abs($diff), 2) . ' from last month';
    }
}
