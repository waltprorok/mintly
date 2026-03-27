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
        $this->month = (int)$data['month'];
        $this->year = (int)$data['year'];

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

        $expensePercent = $income > 0 ? round(($expenses / $income) * 100, 1) : 0;
        $savingsPercent = max(0, 100 - $expensePercent);

        return [
            'datasets' => [
                [
                    'data' => [$expensePercent, $savingsPercent],
                    'backgroundColor' => [
                        'rgba(59,130,246,0.35)', // stronger blue
                        'rgba(34,197,94,0.35)',  // stronger green
                    ],
                    'borderColor' => [
                        'rgba(59,130,246,0.9)',
                        'rgba(34,197,94,0.9)',
                    ],
                    'borderWidth' => 1,
                    'spacing' => 1,
                    'borderRadius' => 3,
                ],
            ],
            'labels' => [
                "Spent ({$expensePercent}%)",
                "Remaining ({$savingsPercent}%)",
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
