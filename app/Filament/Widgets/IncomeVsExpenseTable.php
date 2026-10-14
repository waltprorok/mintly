<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Services\ReportPeriod;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;

class IncomeVsExpenseTable extends TableWidget
{
    protected static ?string $heading = 'Income vs Expenses Breakdown';

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
        if (isset($data['filter'])) {
            $this->filter = $data['filter'];
        } elseif (isset($data['year'], $data['month'])) {
            $this->filter = sprintf(
                '%04d-%02d',
                (int) $data['year'],
                (int) $data['month']
            );
        }

        $this->resetTable();
    }

    protected function getTableQuery(): Builder
    {
        return Transaction::query()->whereRaw('1 = 0');
    }

    public function getTableRecordKey($record): string
    {
        return $record['id'];
    }

    public function getTableRecords(): Collection
    {
        [$start, $end] = ReportPeriod::range($this->filter);

        $income = Transaction::query()
            ->where('user_id', auth()->id())
            ->where('type', 'income')
            ->whereBetween('due_at', [$start, $end])
            ->sum('amount');

        $expenses = Transaction::query()
            ->where('user_id', auth()->id())
            ->where('type', 'expense')
            ->whereBetween('due_at', [$start, $end])
            ->sum('amount');

        $remaining = max(0, $income - $expenses);
        $overspent = max(0, $expenses - $income);

        if ($income <= 0) {
            $expensePercent = 0;
            $remainingPercent = 0;
        } elseif ($expenses <= $income) {
            $expensePercent = round(($expenses / $income) * 100);
            $remainingPercent = 100 - $expensePercent;
        } else {
            $expensePercent = 100;
            $remainingPercent = null;
        }

        $rows = [
            [
                'id' => 'income',
                'label' => 'Income',
                'amount' => $income,
                'percent' => 100,
                'income' => $income,
            ],
            [
                'id' => 'expenses',
                'label' => 'Expenses',
                'amount' => $expenses,
                'percent' => $expensePercent,
                'income' => $income,
            ],
        ];

        if ($overspent > 0) {
            $percentOver = $income > 0
                ? round(($overspent / $income) * 100, 1)
                : 0;

            $rows[] = [
                'id' => 'overspent',
                'label' => 'Overspent',
                'amount' => $overspent,
                'percent' => $percentOver,
                'income' => $income,
            ];
        }

        $rows[] = [
            'id' => 'remaining',
            'label' => 'Remaining',
            'amount' => $remaining,
            'percent' => $remainingPercent,
            'income' => $income,
        ];

        return collect($rows);
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('label')
                ->label('Type')
                ->formatStateUsing(function ($state, $record) {
                    if ($record['id'] === 'overspent') {
                        return new HtmlString("<span style='color:#ef4444;font-weight:600;'>{$state}</span>");
                    }

                    return $state;
                }),

            TextColumn::make('amount')
                ->label('Amount')
                ->formatStateUsing(function ($state, $record) {
                    $formatted = '$' . number_format($state, 2);

                    if ($record['id'] === 'overspent') {
                        return new HtmlString("<span style='color:#ef4444;font-weight:600;'>{$formatted}</span>");
                    }

                    return $formatted;
                }),

            TextColumn::make('percent')
                ->label('Income Usage')
                ->formatStateUsing(function ($state, $record) {
                    if ($state === null) {
                        return '—';
                    }

                    $formatted = $state . '%';

                    if ($record['id'] === 'overspent') {
                        return new HtmlString(
                            "<span style='color:#ef4444;font-weight:600;'>{$formatted}</span>"
                        );
                    }

                    return $formatted;
                }),
        ];
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }
}
