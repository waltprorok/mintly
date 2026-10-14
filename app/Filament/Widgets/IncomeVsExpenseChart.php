<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Services\ReportPeriod;
use Filament\Widgets\ChartWidget;

class IncomeVsExpenseChart extends ChartWidget
{
    protected int|string|array $columnSpan = 1;

    protected ?string $heading = 'Income vs Expenses';

    protected static bool $isDiscovered = false;

    public ?string $filter = null;

    public function mount(): void
    {
        $this->filter = ReportPeriod::latest();
    }

    protected function getFilters(): ?array
    {
        return ReportPeriod::filters();
    }

    protected function getData(): array
    {
        [$start, $end] = ReportPeriod::range($this->filter);

        $income = Transaction::query()
            ->where('user_id', auth()->id())
            ->where('type', 'income')
            ->whereBetween('due_at', [$start, $end])
            ->sum('amount');

        $expenses = Transaction::query()
            ->where('user_id', auth()->id())
            ->where('type', 'expense')
            ->whereBetween('due_at', [$start, $end])
            ->sum('amount');

        $overspend = max(0, $expenses - $income);

        if ($income <= 0) {
            $expensePercent = 0;
            $remainingPercent = 0;
        } elseif ($expenses <= $income) {
            $expensePercent = round(($expenses / $income) * 100, 1);
            $remainingPercent = 100 - $expensePercent;
        } else {
            $expensePercent = 100;
            $remainingPercent = 0;
        }

        return [
            'datasets' => [
                [
                    'data' => [$expensePercent, $remainingPercent],
                    'backgroundColor' => [
                        $overspend > 0
                            ? 'rgba(239,68,68,0.35)'
                            : 'rgba(59,130,246,0.75)',

                        $overspend > 0
                            ? 'rgba(239,68,68,0.15)'
                            : 'rgba(34,197,94,0.75)',
                    ],
                    'spacing' => 0,
                    'borderRadius' => 0,
                    'borderColor' => 'transparent',
                    'borderWidth' => 0,
                ],
            ],
            'labels' => [
                $overspend > 0
                    ? 'Overspent $' . number_format($overspend, 2)
                    : "Spent ({$expensePercent}%)",

                $overspend > 0
                    ? 'Budget Exceeded'
                    : "Remaining ({$remainingPercent}%)",
            ],
        ];
    }

    public function updatedFilter(): void
    {
        $this->dispatch('categoryPeriodChanged', [
            'filter' => $this->filter,
        ]);
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
