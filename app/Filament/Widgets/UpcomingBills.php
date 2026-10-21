<?php

namespace App\Filament\Widgets;

use App\Filament\Tables\Columns\ToggleColumnWithDescription;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class UpcomingBills extends TableWidget
{
    protected int|string|array $columnSpan = 1;

    protected static ?int $sort = 10;

    protected $listeners = [
        'updateBudgetStats' => 'refreshStats',
    ];

    public function refreshStats(): void
    {
        //
    }

    public function getTableHeading(): string
    {
        return 'Upcoming Bills This Week ('
            . now()->format('M j')
            . '-'
            . now()->addDays(6)->format('j')
            . ')';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaction::query()
                    ->where('user_id', auth()->id())
                    ->where('type', 'expense')
                    ->where('is_paid', false)
                    ->whereBetween('due_at', [
                        now()->startOfWeek(),
                        now()->endOfWeek(),
                    ])
                    ->orderBy('due_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('merchant')
                    ->weight(fn ($record) =>
                    $record->due_at?->isSameDay(Carbon::today())
                        ? 'bold'
                        : 'normal'
                    )
                    ->label('Merchant')
                    ->limit(16)
                    ->tooltip(fn($state) => $state)
                    ->description(fn ($record) => $record->nickname),

                Tables\Columns\TextColumn::make('amount')
                    ->weight(fn ($record) =>
                    $record->due_at?->isSameDay(Carbon::today())
                        ? 'bold'
                        : 'normal'
                    )
                    ->money('USD')
                    ->description(fn ($record) => filled($record->payment_method)
                        ? str($record->payment_method)
                            ->replace('_', ' ')
                            ->title()
                            ->toString() : null
                    )
                    ->summarize(
                        Sum::make()
                            ->formatStateUsing(fn($state) => new HtmlString(
                                '<strong>$' . number_format($state, 2) . '</strong>'
                            ))
                            ->html()
                            ->label('Total')
//                            ->money('USD')
                    ),

                Tables\Columns\TextColumn::make('due_at')
                    ->label('Due Date')
                    ->weight(fn ($record) =>
                    $record->due_at?->isToday()
                        ? 'bold'
                        : 'normal'
                    )
                    ->date('M j')
                    ->description(function ($record) {
                        if (! $record->due_at) {
                            return null;
                        }

                        $dueDate = $record->due_at->copy()->startOfDay();
                        $today = Carbon::today();

                        if ($dueDate->isToday()) {
                            return 'due today';
                        }

                        $days = (int) abs($today->diffInDays($dueDate));

                        if ($dueDate->isPast()) {
                            return 'overdue by ' . $days . ' ' . Str::plural('day', $days);
                        }

                        return 'in ' . $days . ' ' . Str::plural('day', $days);
                    }),

                ToggleColumnWithDescription::make('is_paid')
                    ->label('Paid')
                    ->onColor('success')
                    ->offColor('gray')
                    ->description(fn ($record) =>
                    $record->auto_pay ? 'Auto Pay' : 'Manual Pay'
                    )
                    ->afterStateUpdated(function () {
                        $this->dispatch('updateBudgetStats');
                    }),

            ])
            ->defaultPaginationPageOption(10);
    }

}
