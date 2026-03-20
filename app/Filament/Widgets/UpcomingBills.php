<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class UpcomingBills extends TableWidget
{
    protected static ?string $heading = 'Upcoming Bills This Week';

    protected int|string|array $columnSpan = 1;

    protected static ?int $sort = 10;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaction::query()
                    ->where('user_id', auth()->id())
                    ->where('type', 'expense')
                    ->where('status', false)
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
                    ->label('Merchant'),

                Tables\Columns\TextColumn::make('amount')
                    ->weight(fn ($record) =>
                    $record->due_at?->isSameDay(Carbon::today())
                        ? 'bold'
                        : 'normal'
                    )
                    ->money('USD')
                    ->summarize(
                        Sum::make()
                            ->formatStateUsing(fn ($state) => "<strong>$" . number_format($state, 2) . "</strong>")
                            ->html()
                            ->label(' ')
//                            ->money('USD')
                    ),

                Tables\Columns\TextColumn::make('due_at')
                    ->label('Due Date')
                    ->weight(fn ($record) =>
                    $record->due_at?->isSameDay(Carbon::today())
                        ? 'bold'
                        : 'normal'
                    )
                    ->date('M j'),

                ToggleColumn::make('status')
                    ->label('Paid')
                    ->sortable()
                    ->onColor('success')   // green when true
                    ->offColor('gray') ,    // gray when false
            ])
            ->defaultPaginationPageOption(5);
    }

}
