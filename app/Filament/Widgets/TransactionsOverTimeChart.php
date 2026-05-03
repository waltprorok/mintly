<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class TransactionsOverTimeChart extends ChartWidget
{
    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '400px';

    protected ?string $heading = 'Spending Activity by Day';

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

        // Get counts grouped by day
        $results = Transaction::query()
            ->where('user_id', auth()->id())
            ->whereBetween('due_at', [$start, $end])
            ->selectRaw('DAY(due_at) as day, COUNT(*) as count')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('count', 'day');

        $daysInMonth = $start->daysInMonth;

        $labels = [];
        $data = [];
        $colors = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $labels[] = (string) $day;

            $count = $results[$day] ?? 0;
            $data[] = $count;

            $date = Carbon::create($this->year, $this->month, $day);

            // Highlight logic
            if ($count >= 3) {
                // High activity (red)
                $colors[] = 'rgba(239,68,68,0.6)';
            } elseif ($date->isWeekend()) {
                // Weekend (purple tint)
                $colors[] = 'rgba(168,85,247,0.35)';
            } else {
                // Normal (blue)
                $colors[] = 'rgba(59,130,246,0.3)';
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
        $start = Carbon::create($this->year, $this->month)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $results = Transaction::query()
            ->where('user_id', auth()->id())
            ->whereBetween('due_at', [$start, $end])
            ->selectRaw('DAY(due_at) as day, COUNT(*) as count')
            ->groupBy('day')
            ->pluck('count', 'day');

        if ($results->isEmpty()) {
            return 'No transactions this month.';
        }

        $max = $results->max();
        $day = $results->search($max);

        return "Most active day: {$day} ({$max} transactions)";
    }
}
