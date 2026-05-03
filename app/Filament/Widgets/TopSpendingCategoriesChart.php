<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class TopSpendingCategoriesChart extends ChartWidget
{
    protected int|string|array $columnSpan = 1;

    protected ?string $heading = 'Top Spending Categories';

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

        $data = Transaction::query()
            ->where('transactions.user_id', auth()->id())
            ->where('transactions.type', 'expense')
            ->whereBetween('transactions.due_at', [$start, $end])
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->selectRaw('categories.name as category, SUM(transactions.amount) as total')
            ->groupBy('categories.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $labels = $data->pluck('category')->toArray();
        $values = $data->pluck('total')->toArray();

        // Dynamic color generation (balanced + modern)
        $colors = collect($values)
            ->map(fn($_, $i) => "hsl(" . ($i * 50 % 360) . ", 65%, 55%)")
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Amount',
                    'data' => $values,
                    'backgroundColor' => $colors,
                    'spacing' => 4,
                    'borderRadius' => 4,
                    'borderColor' => 'transparent',
                    'borderWidth' => 0,
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
            'indexAxis' => 'y', // horizontal bars
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
        ];
    }
}
