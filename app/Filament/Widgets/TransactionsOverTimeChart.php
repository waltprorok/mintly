<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Services\ReportPeriod;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class TransactionsOverTimeChart extends ChartWidget
{
    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '425px';

    protected ?string $heading = 'Transaction Activity';

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

        $labels = [];
        $data = [];
        $colors = [];

        if ($isYearly) {
            // Yearly: Group transactions by month
            $results = Transaction::query()
                ->where('user_id', auth()->id())
                ->whereBetween('due_at', [$start, $end])
                ->selectRaw('MONTH(due_at) as month, COUNT(*) as count')
                ->groupByRaw('MONTH(due_at)')
                ->orderByRaw('MONTH(due_at)')
                ->pluck('count', 'month');

            $maxCount = max(1, (int) $results->max());

            for ($month = 1; $month <= 12; $month++) {
                $labels[] = $start->copy()->month($month)->format('M');

                $count = (int) ($results[$month] ?? 0);

                $data[] = $count;

                // Relative activity levels for yearly reporting
                if ($count >= $maxCount * 0.75) {
                    $colors[] = 'rgba(239,68,68,0.70)';
                } elseif ($count >= $maxCount * 0.40) {
                    $colors[] = 'rgba(245,158,11,0.70)';
                } else {
                    $colors[] = 'rgba(59,130,246,0.70)';
                }
            }
        } else {
            // Monthly: Group transactions by day
            $results = Transaction::query()
                ->where('user_id', auth()->id())
                ->whereBetween('due_at', [$start, $end])
                ->selectRaw('DAY(due_at) as day, COUNT(*) as count')
                ->groupByRaw('DAY(due_at)')
                ->orderByRaw('DAY(due_at)')
                ->pluck('count', 'day');

            for ($day = 1; $day <= $start->daysInMonth; $day++) {
                $labels[] = (string) $day;

                $count = (int) ($results[$day] ?? 0);

                $data[] = $count;

                $date = $start->copy()->day($day);

                if ($count >= 5) {
                    // High activity → Red
                    $colors[] = 'rgba(239,68,68,0.70)';
                } elseif ($count >= 3) {
                    // Moderate activity → Amber
                    $colors[] = 'rgba(245,158,11,0.70)';
                } elseif ($date->isWeekend()) {
                    // Weekend → Gray
                    $colors[] = 'rgba(156,163,175,0.70)';
                } else {
                    // Normal → Blue
                    $colors[] = 'rgba(59,130,246,0.70)';
                }
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Transactions',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderColor' => 'rgba(59,130,246,0.9)',
                    'borderWidth' => 0,
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                        'stepSize' => 1,
                    ],
                ],
            ],
            'plugins' => [
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'Transactions: ' . '${context.raw}',
                    ],
                ],
                'legend' => [
                    'display' => false,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    public function getDescription(): ?string
    {
        [$start, $end] = ReportPeriod::range($this->filter);

        $isYearly = str_starts_with(
            $this->filter ?? ReportPeriod::latest(),
            'year-'
        );

        if ($isYearly) {
            $results = Transaction::query()
                ->where('user_id', auth()->id())
                ->whereBetween('due_at', [$start, $end])
                ->selectRaw('MONTH(due_at) as month, COUNT(*) as count')
                ->groupByRaw('MONTH(due_at)')
                ->pluck('count', 'month');

            if ($results->isEmpty()) {
                return 'No transactions this year.';
            }

            $max = $results->max();
            $month = (int) $results->search($max);

            $monthName = $start->copy()
                ->month($month)
                ->format('F');

            return "Most active month: {$monthName} ({$max} transactions) — High: Red | Moderate: Amber | Normal: Blue";
        }

        // Monthly description
        $results = Transaction::query()
            ->where('user_id', auth()->id())
            ->whereBetween('due_at', [$start, $end])
            ->selectRaw('DAY(due_at) as day, COUNT(*) as count')
            ->groupByRaw('DAY(due_at)')
            ->pluck('count', 'day');

        if ($results->isEmpty()) {
            return 'No transactions this month.';
        }

        $max = $results->max();
        $day = $results->search($max);

        return "Most active day: {$day} ({$max} transactions) — High: Red | Moderate: Amber | Weekend: Gray | Normal: Blue";
    }
}
