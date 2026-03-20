<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class CategoryBreakdownChart extends ChartWidget
{
    protected ?string $heading = 'Expenses by Category';

    protected ?string $maxHeight = '720px';

    protected int|string|array $columnSpan = 2;

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
            ->map(fn ($items) => $items->sum('amount'))
            ->sortDesc();

        $values = $data->values()->toArray();
        $labels = $data->keys()->toArray();

        $colors = collect($values)
            ->map(fn ($_,$i) => "hsl(" . ($i * 40 % 360) . ", 70%, 50%)")
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Expenses',
                    'data' => $values,
//                    'backgroundColor' => $colors,
//                    'borderWidth' => 0,
//                    'hoverBorderWidth' => 0,
                    'borderRadius' => 6,

                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59,130,246,0.15)',
                    'tension' => 0.2,
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
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
            $key = $start->format('Y-m'); // e.g. 2026-03
            $label = $start->format('F Y'); // March 2026

            $periods[$key] = $label;

            $start->addMonth();
        }

        return collect($periods)->reverse()->toArray();
    }

//    public function getHeading(): ?string
//    {
//        [$year, $month] = $this->filter
//            ? explode('-', $this->filter)
//            : [now()->year, now()->month];
//
//        return 'Spending by Category — ' . Carbon::create($year, $month)->format('F Y');
//    }
}
