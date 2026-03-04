<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ExpenseCategoryChart extends ChartWidget
{
    protected ?string $heading = 'Expenses by Category';

    protected ?string $maxHeight = '360px';

    protected int|string|array $columnSpan = 1;

    protected static ?int $sort = 6;

    protected function getType(): string
    {
        return 'doughnut';
    }

    public function getHeading(): string
    {
        return 'Expense Breakdown | ' . now()->format('F Y');
    }

    protected function getData(): array
    {
        $data = Transaction::query()
            ->select('categories.name', DB::raw('SUM(transactions.amount) as total'))
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.user_id', auth()->id())
            ->where('transactions.type', 'expense')
            ->whereBetween('transactions.due_at', [
                now()->startOfMonth(),
                now()->endOfMonth(),
            ])
            ->groupBy('categories.name')
            ->orderByDesc('total')
            ->get();

        $labels = $data->pluck('name');
        $values = $data->pluck('total');

        $count = $labels->count();

        $colors = collect(range(0, $count - 1))->map(function ($i) use ($count) {
            $hue = ($i * (360 / max($count, 1)));
            return "hsl({$hue}, 70%, 55%)";
        });

        return [
            'datasets' => [
                [
                    'data' => $values,
                    'backgroundColor' => $colors,
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
