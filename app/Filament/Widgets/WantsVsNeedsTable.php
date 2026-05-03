<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;

class WantsVsNeedsTable extends TableWidget
{
    protected int|string|array $columnSpan = 1;

    protected static ?string $heading = 'Needs vs Wants Breakdown';

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

    protected function getTotalSum(): float
    {
        return $this->getTableRecords()->sum('total');
    }

    protected function getTotalIncome(): float
    {
        return Transaction::query()
            ->where('user_id', auth()->id())
            ->where('type', 'income')
            ->whereMonth('due_at', $this->month)
            ->whereYear('due_at', $this->year)
            ->sum('amount');
    }

    public function getTableRecords(): Collection
    {
        $income = $this->getTotalIncome();

        return Transaction::query()
            ->where('user_id', auth()->id())
            ->where('type', 'expense')
            ->whereMonth('due_at', $this->month)
            ->whereYear('due_at', $this->year)
            ->with('category')
            ->get()
            ->groupBy(function ($record) {
                $category = $record->category;

                return ($category->spend_classification ?? 'unknown') . '|' . ($category->name ?? 'Uncategorized');
            })
            ->map(function ($items, $key) use ($income) {
                [$classification, $category] = explode('|', $key);

                $total = $items->sum('amount');

                return [
                    'id' => md5($key),
                    'category_name' => $category,
                    'classification' => $classification,
                    'total' => $total,
                    'percent' => $income > 0 ? round(($total / $income) * 100) : 0,
                ];
            })
            ->sortByDesc('total')
            ->values();
    }

    protected function getTableColumns(): array
    {
        return [

            TextColumn::make('category_name')
                ->label('Category'),

            TextColumn::make('classification')
                ->label('Type')
                ->formatStateUsing(function ($state) {
                    return match ($state) {
                        'non_discretionary' => new HtmlString("<span style='color:#22c55e;font-weight:600;'>Needs</span>"),
                        'discretionary' => new HtmlString("<span style='color:#ef4444;font-weight:600;'>Wants</span>"),
                        default => new HtmlString("<span style='color:#9ca3af;'>Unknown</span>"),
                    };
                }),

            TextColumn::make('total')
                ->label('Amount')
                ->money('USD'),

            TextColumn::make('percent')
                ->label('Income Usage')
                ->alignCenter()
                ->tooltip('Percentage of your total monthly income')
                ->formatStateUsing(fn ($state) => $state . '%')
                ->summarize([
                    Summarizer::make()
                        ->label('Income Used')
                        ->formatStateUsing(function () {
                            $income = $this->getTotalIncome();
                            $expenses = $this->getTotalSum();

                            $percent = $income > 0
                                ? round(($expenses / $income) * 100)
                                : 0;

                            if ($percent > 100) {
                                return new HtmlString(
                                    "<span style='color:#ef4444;font-weight:600;'>
                                        {$percent}% (Over Budget)
                                    </span>"
                                );
                            }

                            return "{$percent}%";
                        })
                ]),

            TextColumn::make('total')
                ->label('Amount')
                ->money('USD')
                ->summarize([
                    Summarizer::make()
                        ->label('Total')
                        ->formatStateUsing(function () {
                            $total = $this->getTotalSum();
                            $income = $this->getTotalIncome();
                            $formatted = '$' . number_format($total, 2);

                            if ($income > 0 && $total > $income) {
                                return new HtmlString(
                                    "<span style='color:#ef4444;font-weight:600;'>{$formatted}</span>"
                                );
                            }

                            return $formatted;
                        })
                ]),
        ];
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }
}
