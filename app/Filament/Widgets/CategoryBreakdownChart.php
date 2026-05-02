<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class CategoryBreakdownChart extends ChartWidget
{
    protected int|string|array $columnSpan = 1;

    protected ?string $heading = 'Expenses by Category';

    protected static bool $isDiscovered = false;

    public ?string $filter = null;

    public function mount(): void
    {
        $this->filter = now()->format('Y-m');
    }

    protected function getData(): array
    {
        [$year, $month] = $this->filter
            ? explode('-', $this->filter)
            : [now()->year, now()->month];

        $data = Transaction::query()
            ->where('user_id', auth()->id())
            ->where('type', 'expense')
            ->whereMonth('due_at', $month)
            ->whereYear('due_at', $year)
            ->with('category')
            ->get()
            ->groupBy('category.name')
            ->map(fn($items) => $items->sum('amount'))
            ->sortDesc();

        $values = $data->values()->toArray();

        $income = Transaction::query()
            ->where('user_id', auth()->id())
            ->where('type', 'income')
            ->whereMonth('due_at', $month)
            ->whereYear('due_at', $year)
            ->sum('amount');

        $labels = $data->map(function ($amount, $label) use ($income) {
            $percent = $income > 0 ? round(($amount / $income) * 100, 1) : 0;
            return "{$label} ({$percent}%)";
        })->values()->toArray();

        $colors = collect($values)
            ->map(fn($_, $i) => "hsl(" . ($i * 40 % 360) . ", 70%, 55%)")
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Expenses',
                    'data' => $values,
                    'backgroundColor' => $colors,
                    'tension' => 0.2,
                    'fill' => true,
                    'borderColor' => 'transparent',
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }

    protected function getFilters(): ?array
    {
        $oldest = Transaction::where('user_id', auth()->id())->min('due_at');
        $newest = Transaction::where('user_id', auth()->id())->max('due_at');

        $start = $oldest ? Carbon::parse($oldest)->startOfMonth() : now()->startOfMonth();
        $end = $newest ? Carbon::parse($newest)->startOfMonth() : now()->startOfMonth();

        $periods = [];

        while ($start <= $end) {
            $key = $start->format('Y-m');
            $label = $start->format('F Y');

            $periods[$key] = $label;

            $start->addMonth();
        }

        return collect($periods)->reverse()->toArray();
    }

    public function updatedFilter(): void
    {
        [$year, $month] = $this->filter
            ? explode('-', $this->filter)
            : [now()->year, now()->month];

        $this->dispatch('categoryPeriodChanged', [
            'month' => $month,
            'year' => $year,
        ]);
    }
}
