<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;

class IncomeVsExpenseTable extends TableWidget
{
    protected static ?string $heading = 'Income vs Expenses Table';

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
        $this->month = (int) $data['month'];
        $this->year = (int) $data['year'];

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

        $expensePercent = $income > 0 ? round(($expenses / $income) * 100, 1) : 0;
        $remainingPercent = max(0, 100 - $expensePercent);

        return collect([
            [
                'id' => 'expenses',
                'label' => 'Expenses',
                'amount' => $expenses,
                'percent' => $expensePercent,
            ],
            [
                'id' => 'remaining',
                'label' => 'Remaining',
                'amount' => $remaining,
                'percent' => $remainingPercent,
            ],
        ]);
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('label')
                ->label('Type'),

            TextColumn::make('amount')
                ->label('Amount')
                ->money('USD'),

            TextColumn::make('percent')
                ->label('Percent')
                ->formatStateUsing(fn ($state) => $state . '%')
                ->summarize([
                    Summarizer::make()
                        ->label('Summary')
                        ->formatStateUsing(function () {
                            $records = $this->getTableRecords();

                            $expenses = $records->firstWhere('id', 'expenses')['percent'] ?? 0;
                            $remaining = $records->firstWhere('id', 'remaining')['percent'] ?? 0;

                            return new HtmlString(
                                "<strong>{$expenses}% Expenses vs {$remaining}% Remaining</strong>"
                            );
                        }),
                ]),
        ];
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }
}
