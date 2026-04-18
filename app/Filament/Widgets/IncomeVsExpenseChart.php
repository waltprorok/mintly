<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class IncomeVsExpenseChart extends ChartWidget
{
    protected int|string|array $columnSpan = 1;

    protected ?string $heading = 'Income vs Expenses';

    protected static bool $isDiscovered = false;

    public int $month;
    public int $year;

    public function mount(): void
    {
        $this->month = now()->month;
        $this->year = now()->year;
    }

    #[On('categoryPeriodChanged')]
    public function updatePeriod($data): void
    {
        $this->month = (int) $data['month'];
        $this->year = (int) $data['year'];

        $this->dispatch('$refresh');
    }

    protected function getData(): array
    {
        $start = Carbon::create($this->year, $this->month)->startOfMonth();
        $end = $start->copy()->endOfMonth();

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
        // normalize chart to 100% and handle overspending
        if ($income <= 0) {
            $expensePercent = 0;
            $remainingPercent = 0;
        } elseif ($expenses <= $income) {
            $expensePercent = round(($expenses / $income) * 100, 1);
            $remainingPercent = 100 - $expensePercent;
        } else {
            // overspending → cap chart at 100%
            $expensePercent = 100;
            $remainingPercent = 0;
        }

        return [
            'datasets' => [
                [
                    'data' => [$expensePercent, $remainingPercent],
                    'backgroundColor' => [
                        // turn red when overspending
                        $overspend > 0
                            ? 'rgba(239,68,68,0.35)'   // red
                            : 'rgba(59,130,246,0.35)', // blue

                        $overspend > 0
                            ? 'rgba(239,68,68,0.15)'   // faded red
                            : 'rgba(34,197,94,0.35)',  // green
                    ],
                    'borderColor' => [
                        $overspend > 0
                            ? 'rgba(239,68,68,0.9)'
                            : 'rgba(59,130,246,0.9)',

                        $overspend > 0
                            ? 'rgba(239,68,68,0.5)'
                            : 'rgba(34,197,94,0.9)',
                    ],
                    'borderWidth' => 1,
                    'spacing' => 1,
                    'borderRadius' => 3,
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

    protected function getType(): string
    {
        return 'doughnut';
    }
}
