<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Services\ReportPeriod;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class MonthlySavingsRateChart extends ChartWidget
{
    protected ?string $heading = 'Net Cash Flow Trend';

    protected int|string|array $columnSpan = 1;

    protected static bool $isDiscovered = false;

    public ?string $filter = null;

    public function mount(): void
    {
        $this->filter = ReportPeriod::latest();
    }

    #[On('categoryPeriodChanged')]
    public function updatePeriod(array $data): void
    {
        $this->filter = $data['filter'] ?? ReportPeriod::latest();

        $this->dispatch('$refresh');
    }

    protected function getData(): array
    {
        [$start, $end] = ReportPeriod::range($this->filter);

        $isYearly = str_starts_with(
            $this->filter ?? ReportPeriod::latest(),
            'year-'
        );

        // Yearly: January through December
        // Monthly: Last 6 months ending with the selected month
        $months = $isYearly ? 12 : 6;

        $endMonth = $end->copy()->startOfMonth();

        $labels = [];
        $incomeData = [];
        $expenseData = [];
        $netData = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = $endMonth->copy()->subMonthsNoOverflow($i);

            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();

            $income = Transaction::query()
                ->where('user_id', auth()->id())
                ->where('type', 'income')
                ->whereBetween('due_at', [$monthStart, $monthEnd])
                ->sum('amount');

            $expenses = Transaction::query()
                ->where('user_id', auth()->id())
                ->where('type', 'expense')
                ->whereBetween('due_at', [$monthStart, $monthEnd])
                ->sum('amount');

            // Skip months without transactions
            if ($income <= 0 && $expenses <= 0) {
                continue;
            }

            $labels[] = $date->format('M');

            $incomeData[] = round($income, 2);
            $expenseData[] = round($expenses, 2);
            $netData[] = round($income - $expenses, 2);
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
