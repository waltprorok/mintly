<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;

class IncomeExpenseChart extends ChartWidget
{
    protected ?string $heading = 'Income vs Expenses';

    protected ?string $maxHeight = '350px';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 10;

    public ?string $filter = '12';

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
                    'fill' => false,
                ],
                [
                    'label' => 'Expenses',
                    'data' => $expenseData,
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239,68,68,0.15)',
                    'tension' => 0.2,
                    'fill' => false,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
