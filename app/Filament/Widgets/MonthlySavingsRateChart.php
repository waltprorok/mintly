<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class MonthlySavingsRateChart extends ChartWidget
{
    protected ?string $heading = 'Net Cash Flow Trend';

    protected int|string|array $columnSpan = 1;

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
        $labels = [];

        $incomeData = [];

        $expenseData = [];

        $netData = [];

        for ($i = 5; $i >= 0; $i--) {

            $date = Carbon::create($this->year, $this->month)
                ->subMonths($i);

            $start = $date->copy()->startOfMonth();

            $end = $date->copy()->endOfMonth();

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

            $net = $income - $expenses;
            // Skip completely empty months
            if ($income <= 0 && $expenses <= 0) {
                continue;
            }

            $labels[] = $date->format('M');
            $incomeData[] = round($income, 2);
            $expenseData[] = round($expenses, 2);
            $netData[] = round($net, 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Income',
                    'data' => $incomeData,
                    'borderColor' => 'rgba(34,197,94,1)',
                    'backgroundColor' => 'rgba(34,197,94,0.15)',
                    'tension' => 0.3,
                    'borderWidth' => 3,
                ],
                [
                    'label' => 'Expenses',
                    'data' => $expenseData,
                    'borderColor' => 'rgba(239,68,68,1)',
                    'backgroundColor' => 'rgba(239,68,68,0.15)',
                    'tension' => 0.3,
                    'borderWidth' => 3,
                ],
                [
                    'label' => 'Net',
                    'data' => $netData,
                    'borderColor' => 'rgba(59,130,246,1)',
                    'backgroundColor' => 'rgba(59,130,246,0.15)',
                    'tension' => 0.3,
                    'borderWidth' => 5,

                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],

            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
