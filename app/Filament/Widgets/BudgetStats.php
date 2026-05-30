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

    public function updateBudgetStats(?int $month = null, ?int $year = null): void
    {
        if ($month !== null) {
            $this->month = $month;
        }

        if ($year !== null) {
            $this->year = $year;
        }
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
//        $incomeChangeValue = $this->percentChangeValue($income, $prevIncome);
//        $expenseChangeValue = $this->percentChangeValue($expenses, $prevExpenses);
//        $netPercentChangeValue = $this->percentChangeValue($net, $prevNet);

        // Dollar differences
        $incomeDiff = $income - $prevIncome;
        $expenseDiff = $expenses - $prevExpenses;
        $netDiff = $net - $prevNet;

        // Descriptions (NEW FORMAT)
        $incomeChange = $this->formatFullChange($incomeDiff, $prevIncome);
        $expenseChange = $this->formatFullChange($expenseDiff, $prevExpenses);
        $netChange = $this->formatFullChange($netDiff, $prevNet);

        // UI state (handle "no previous data")
        $incomeIcon = null;
        $incomeColor = 'gray';

        if ($prevIncome != 0) {
            $incomeIcon = $incomeDiff < 0
                ? 'heroicon-m-arrow-trending-down'
                : 'heroicon-m-arrow-trending-up';

            $incomeColor = $incomeDiff < 0 ? 'danger' : 'success';
        }

        $expenseIcon = null;
        $expenseColor = 'gray';

        if ($prevExpenses != 0) {
            $expenseIcon = $expenseDiff > 0
                ? 'heroicon-m-arrow-trending-up'
                : 'heroicon-m-arrow-trending-down';

            $expenseColor = $expenseDiff > 0 ? 'danger' : 'success';
        }

        $netIcon = null;
        $netColor = 'gray';

        if ($prevNet != 0) {
            $netIcon = $netDiff < 0
                ? 'heroicon-m-arrow-trending-down'
                : 'heroicon-m-arrow-trending-up';

            $netColor = $netDiff < 0 ? 'danger' : 'success';
        }

        return [
            // Income (up = good)
            Stat::make('Monthly Income', '$' . number_format($income, 2))
                ->description($incomeChange)
                ->descriptionIcon($incomeIcon)
                ->color($incomeColor),

            // Expenses (down = good)
            Stat::make('Monthly Expenses', '$' . number_format($expenses, 2))
                ->description($expenseChange)
                ->descriptionIcon($expenseIcon)
                ->color($expenseColor),

            // Net (now consistent with others)
            Stat::make('Net This Month', '$' . number_format($net, 2))
                ->description($netChange)
                ->descriptionIcon($netIcon)
                ->color($netColor),

            // Paid bills
            Stat::make('Bills Paid', '$' . number_format($paidExpenses, 2))
                ->description($paidPercent . '% of expenses paid')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            // Outstanding
            Stat::make('Recurring Bills', '$' . number_format($unpaidExpenses, 2))
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

//    private function percentChangeValue($current, $previous): float
//    {
//        if ($previous == 0) {
//            return 0;
//        }
//
//        return (($current - $previous) / $previous) * 100;
//    }

    private function formatFullChange($diff, $previous): string
    {
        if ($previous == 0) {
            return 'No data from last month';
        }

        $prefix = $diff < 0 ? '-' : '+';

        return sprintf(
            '%s$%s from last month',
            $prefix,
            number_format(abs($diff), 2)
        );
    }
}
