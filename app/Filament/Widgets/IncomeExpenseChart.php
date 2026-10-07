<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;

class IncomeExpenseChart extends ChartWidget
{
    protected ?string $heading = 'Income vs Expenses';

    protected ?string $maxHeight = '350px';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 4;

    public ?string $filter = '3';

    protected function getFilters(): ?array
    {
        return [
            '3' => 'Last 3 Months',
            '6' => 'Last 6 Months',
            '12' => 'Last 12 Months',
        ];
    }

    protected function getData(): array
    {
        $monthsToShow = (int) ($this->filter ?? 12);

        $userId = auth()->id();

        $months = collect(range(0, $monthsToShow - 1))
            ->map(fn ($i) => now()->subMonths($i)->startOfMonth())
            ->reverse()
            ->values();

        $labels = [];
        $incomeData = [];
        $expenseData = [];

        foreach ($months as $month) {

            $labels[] = $month->format('M Y');

            $incomeData[] = Transaction::query()
                ->where('user_id', $userId)
                ->where('type', 'income')
                ->whereYear('due_at', $month->year)
                ->whereMonth('due_at', $month->month)
                ->sum('amount');

            $expenseData[] = Transaction::query()
                ->where('user_id', $userId)
                ->where('type', 'expense')
                ->whereYear('due_at', $month->year)
                ->whereMonth('due_at', $month->month)
                ->sum('amount');
        }

        $expenseBorderColors = [];
        $expenseBackgroundColors = [];
        $expenseHoverBorderColors = [];
        $expenseHoverBackgroundColors = [];

        foreach ($expenseData as $index => $expense) {
            $overIncome = $expense > $incomeData[$index];

            $expenseBorderColors[] = $overIncome
                ? '#ef4444'
                : '#3b82f6';

            $expenseBackgroundColors[] = $overIncome
                ? 'rgba(239,68,68,0.15)'
                : 'rgba(59,130,246,0.15)';

            $expenseHoverBorderColors[] = $overIncome
                ? '#ef4444'
                : '#3b82f6';

            $expenseHoverBackgroundColors[] = $overIncome
                ? 'rgba(239,68,68,0.25)'
                : 'rgba(59,130,246,0.25)';
        }

        return [
            'datasets' => [
                [
                    'label' => 'Income',
                    'data' => $incomeData,
                    'borderColor' => '#22c55e',
                    'backgroundColor' => 'rgba(34,197,94,0.2)',
                    'hoverBorderColor' => '#22c55e',
                    'hoverBackgroundColor' => 'rgba(34,197,94,0.25)',
                    'tension' => 0.2,
                    'borderRadius' => 6,
                    'fill' => false,
                ],
                [
                    'label' => 'Expenses',
                    'data' => $expenseData,
                    'borderColor' => $expenseBorderColors,
                    'backgroundColor' => $expenseBackgroundColors,
                    'hoverBorderColor' => $expenseHoverBorderColors,
                    'hoverBackgroundColor' => $expenseHoverBackgroundColors,
                    'tension' => 0.2,
                    'borderRadius' => 6,
                    'fill' => false,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<'JS'
        {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        'callback': (value) => '$' + value.toLocaleString()
                    }
                }
            }
        }
    JS);
    }
}
