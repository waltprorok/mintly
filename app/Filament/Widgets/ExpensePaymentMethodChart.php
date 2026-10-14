<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Services\ReportPeriod;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class ExpensePaymentMethodChart extends ChartWidget
{
    protected ?string $heading = 'How You Pay';

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

        $totals = Transaction::query()
            ->where('user_id', auth()->id())
            ->where('type', 'expense')
            ->whereBetween('due_at', [$start, $end])
            ->selectRaw('
                COALESCE(payment_method, "Other") as payment_method,
                SUM(amount) as total
            ')
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->pluck('total', 'payment_method');

        $grandTotal = $totals->sum();

        $labels = [];
        $data = [];
        $colors = [];

        foreach ($totals as $method => $amount) {

            $percent = $grandTotal > 0
                ? round(($amount / $grandTotal) * 100, 1)
                : 0;

            $methodLabel = match (strtolower($method)) {
                'credit_card' => 'Credit Card',
                'bank' => 'Bank',
                'cash' => 'Cash',
                default => 'Other',
            };

            $labels[] = [
                "{$methodLabel} ({$percent}%)",
                ' $' . number_format($amount, 2),
            ];

            $data[] = round($amount, 2);

            $colors[] = match (strtolower($method)) {
                'credit_card' => 'rgba(59,130,246,0.75)', // blue
                'bank' => 'rgba(34,197,94,0.75)',         // green
                'cash' => 'rgba(245,158,11,0.75)',        // amber
                default => 'rgba(156,163,175,0.55)',      // gray
            };
        }

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderSkipped' => false,
                    'spacing' => 0,
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
            'indexAxis' => 'y',
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
