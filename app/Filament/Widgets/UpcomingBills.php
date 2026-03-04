<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Tables;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class UpcomingBills extends TableWidget
{
    protected static ?string $heading = 'Upcoming Bills';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 4;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaction::query()
                    ->where('user_id', auth()->id())
                    ->where('type', 'expense')
                    ->where('status', false)
                    ->whereBetween('due_at', [
                        now(),
                        now()->addDays(7)
                    ])
                    ->orderBy('due_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('merchant')
                    ->label('Merchant')
                    ->searchable(),

                Tables\Columns\TextColumn::make('amount')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_at')
                    ->label('Due Date')
                    ->date('M j')
                    ->sortable(),

                ToggleColumn::make('status')
                    ->label('Paid')
                    ->sortable()
                    ->onColor('success')   // green when true
                    ->offColor('gray') ,    // gray when false
            ])
            ->defaultPaginationPageOption(5);
    }
}
