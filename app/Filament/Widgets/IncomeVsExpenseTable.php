<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
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
        $this->month = (int)$data['month'];
        $this->year = (int)$data['year'];

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
        $start = Carbon::create($this->year, $this->month)->startOfMonth();
        $end = $start->copy()->endOfMonth();

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
