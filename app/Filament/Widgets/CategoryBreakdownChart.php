<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Services\ReportPeriod;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class CategoryBreakdownChart extends ChartWidget
{
    protected int|string|array $columnSpan = 1;

    protected ?string $heading = 'Expenses by Category';

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

        // Aggregate expenses by category
        $data = Transaction::query()
            ->where('transactions.user_id', auth()->id())
            ->where('transactions.type', 'expense')
            ->whereBetween('transactions.due_at', [$start, $end])
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->selectRaw('categories.name as category, SUM(transactions.amount) as total')
            ->groupBy('categories.name')
            ->orderByDesc('total')
            ->pluck('total', 'category');

        $values = $data->values()->toArray();

        // Income (for % calc)
        $income = Transaction::query()
            ->where('user_id', auth()->id())
            ->where('type', 'income')
            ->whereBetween('due_at', [$start, $end])
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

}
