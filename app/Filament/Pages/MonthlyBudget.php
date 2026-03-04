<?php

namespace App\Filament\Pages;

use App\Models\Transaction;
use Filament\Pages\Page;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class MonthlyBudget extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.monthly-budget';

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-table-cells';

    protected static ?string $slug = 'budget';

    protected static bool $shouldRegisterNavigation = true;

    protected static string|null|\UnitEnum $navigationGroup = 'Mintly';

    protected static ?string $navigationLabel = 'Budget Planner';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Monthly Budget';


    public string $income;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaction::query()
                    ->with('category')
                    ->where('user_id', auth()->id())
                    ->where('type', 'expense')
                    ->whereMonth('due_at', now()->month)
                    ->whereYear('due_at', now()->year)
                    ->select('*')
                    ->selectRaw("((cast(strftime('%d', due_at) as integer) - 1) / 7) + 1 as week"),
            )
            ->defaultSort('due_at')
            ->groups([
                Group::make('category.name')
                    ->label('Category')
                    ->collapsible(),
            ])
            ->defaultGroup('category.name')
            ->paginated(false)
            ->columns([
                TextColumn::make('due_at')
                    ->label('Date')
                    ->date('m/d/y')
                    ->sortable(),

                TextColumn::make('merchant')
                    ->label('Description')
                    ->default(fn($record) => $record->category->name),

                TextColumn::make('amount')
                    ->money('USD')
                    ->alignRight()
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->money('USD')
                            ->label('Total Expenses')
                    ),

                ...collect(range(1, 4))->map(
                    fn ($week) => TextColumn::make("week{$week}")
                        ->label("Week {$week}")
                        ->alignRight()
                        ->money('USD')
                        ->getStateUsing(fn ($record) =>
                        $record->week == $week ? $record->amount : null
                        )
                )->all(),

                TextColumn::make('payment_method')
                    ->label('Payment')
                    ->formatStateUsing(fn($state) => str($state)->replace('_', ' ')->title()
                    ),

                IconColumn::make('status')
                    ->label('Paid')
                    ->boolean(),
            ]);
    }

    public function mount(): void
    {
        $month = now()->month;
        $year = now()->year;

        // income
        $this->income = Transaction::query()
            ->where('user_id', auth()->id())
            ->where('type', 'income')
            ->whereMonth('due_at', $month)
            ->whereYear('due_at', $year)
            ->sum('amount');

    }
}
