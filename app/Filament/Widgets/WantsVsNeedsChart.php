<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class WantsVsNeedsChart extends ChartWidget
{
    protected int|string|array $columnSpan = 1;

    protected ?string $heading = 'Needs vs Wants';

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

        $totals = Transaction::query()
            ->where('transactions.user_id', auth()->id())
            ->where('transactions.type', 'expense')
            ->whereBetween('transactions.due_at', [$start, $end])
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->select('categories.spend_classification')
            ->selectRaw('SUM(transactions.amount) as total')
            ->groupBy('categories.spend_classification')
            ->pluck('total', 'categories.spend_classification');

        $needs = $totals['non_discretionary'] ?? 0;
        $wants = $totals['discretionary'] ?? 0;
        $unknown = $totals['unknown'] ?? 0;

        $total = $needs + $wants + $unknown;

        if ($total <= 0) {
            $needsPercent = 0;
            $wantsPercent = 0;
            $unknownPercent = 0;
        } else {
            // Round primary values
            $needsPercent = round(($needs / $total) * 100, 1);
            $wantsPercent = round(($wants / $total) * 100, 1);

            // Force total to equal 100% cleanly
            $unknownPercent = max(0, round(100 - ($needsPercent + $wantsPercent), 1));

            // Clamp tiny floating noise
            if ($unknownPercent < 0.5) {
                $unknownPercent = 0;
            }
        }

        return [
            'datasets' => [
                [
                    'data' => [$needs, $wants, $unknown],
                    'backgroundColor' => [
                        'rgba(34,197,94,0.75)',   // Needs (green)
                        'rgba(245,158,11,0.75)',  // Wants (orange)
                        'rgba(156,163,175,0.35)', // Unknown (gray)
                    ],
                    'spacing' => 0,
                    'borderRadius' => 4,
                    'borderColor' => 'transparent',
                    'borderWidth' => 0,
                ],
            ],
            'labels' => [
                "Needs ({$needsPercent}%)",
                "Wants ({$wantsPercent}%)",
                "Unknown ({$unknownPercent}%)",
            ],
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
