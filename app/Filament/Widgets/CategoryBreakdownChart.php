<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class CategoryBreakdownChart extends ChartWidget
{
    protected int|string|array $columnSpan = 1;

    protected ?string $heading = 'Expenses by Category';

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
        $month = $this->month;
        $year = $this->year;

        // Aggregate in DB (faster + cleaner)
        $data = Transaction::query()
            ->where('transactions.user_id', auth()->id())
            ->where('transactions.type', 'expense')
            ->whereMonth('transactions.due_at', $month)
            ->whereYear('transactions.due_at', $year)
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
