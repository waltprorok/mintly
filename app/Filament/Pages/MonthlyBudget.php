<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\BudgetStats;
use App\Filament\Widgets\WeeklyCashFlowStats;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class MonthlyBudget extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.monthly-budget';

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-table-cells';

    protected static ?string $slug = 'budget';

    protected static bool $shouldRegisterNavigation = true;

//    protected static string|null|\UnitEnum $navigationGroup = 'Mintly';

    protected static ?string $navigationLabel = 'Budget Planner';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Monthly Budget';

    public int $month;

    public int $year;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaction::query()
                    ->with('category')
                    ->where('user_id', auth()->id())
                    ->whereIn('type', ['income', 'expense'])
                    ->select('*')
                    ->selectRaw("((cast(strftime('%d', due_at) as integer) - 1) / 7) + 1 as week")
                    ->orderByRaw("CASE WHEN type = 'income' THEN 0 ELSE 1 END")
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
                    ->sortable()
                    ->summarize([
                        Sum::make()
                            ->label('')
                            ->formatStateUsing(function ($state, $query) {
                                $income = (clone $query)
                                    ->where('type', 'income')
                                    ->sum('amount');

                                $expenses = (clone $query)
                                    ->where('type', 'expense')
                                    ->sum('amount');

                                if ($income > 0) {
                                    return "<strong>$" . number_format($income, 2) . " Income</strong>";
                                }

                                return "<strong>$" . number_format($expenses, 2) . " </strong>";
                            }),

                    ]),

                ...collect(range(1, 4))->map(
                    fn($week) => TextColumn::make("week{$week}")
                        ->label("Week {$week}")
                        ->money('USD')
                        ->getStateUsing(fn($record) => $record->week == $week ? $record->amount : null
                        )
                )->all(),

                TextColumn::make('payment_method')
                    ->label('Payment')
                    ->formatStateUsing(fn($state) => str($state)->replace('_', ' ')->title()
                    ),

                IconColumn::make('status')
                    ->label('Paid')
                    ->boolean(),
            ])
            ->filters([
                Filter::make('period')
                    ->label('Period')
                    ->default([
                        'month' => now()->month,
                        'year' => now()->year,
                    ])
                    ->form([
                        Select::make('month')
                            ->label('Month')
                            ->options([
                                1 => 'January',
                                2 => 'February',
                                3 => 'March',
                                4 => 'April',
                                5 => 'May',
                                6 => 'June',
                                7 => 'July',
                                8 => 'August',
                                9 => 'September',
                                10 => 'October',
                                11 => 'November',
                                12 => 'December',
                            ])
                            ->default(now()->month),

                        Select::make('year')
                            ->label('Year')
                            ->options(
                                collect(range(now()->year - 1, now()->year + 1))
                                    ->mapWithKeys(fn($y) => [$y => $y])
                            )
                            ->default(now()->year),
                    ])
                    ->query(function ($query, array $data) {
                        $this->month = $data['month'] ?? now()->month;
                        $this->year = $data['year'] ?? now()->year;

                        $this->dispatch('updateBudgetStats',
                            month: $this->month,
                            year: $this->year
                        );

                        return $query
                            ->whereMonth('due_at', $this->month)
                            ->whereYear('due_at', $this->year);
                    })
                    ->indicateUsing(function (array $data) {
                        $month = $data['month'] ?? now()->month;
                        $year = $data['year'] ?? now()->year;

                        return Carbon::create($year, $month)->format('F Y');
                    }),
            ])
            ->persistFiltersInSession();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('roll_forward')
                ->label('Prepare Next Month')
                ->icon('heroicon-o-arrow-right-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Prepare Next Month')
                ->modalDescription('This will copy ALL RECURRING transactions from the current month into next month.')
                ->modalSubmitActionLabel('Copy Transactions')
                ->action(function () {
                    $currentMonth = Carbon::create($this->year, $this->month);
                    $nextMonth = $currentMonth->copy()->addMonth();
                    $lastDay = $nextMonth->copy()->endOfMonth()->day;

                    $transactions = Transaction::query()
                        ->where('user_id', auth()->id())
                        ->where('is_recurring', true)
                        ->whereMonth('due_at', $this->month)
                        ->whereYear('due_at', $this->year)
                        ->get();

                    $count = 0;

                    foreach ($transactions as $transaction) {
                        $day = $transaction->due_at->day;

                        $newDay = min($day, $lastDay);

                        $newDate = Carbon::create(
                            $nextMonth->year,
                            $nextMonth->month,
                            $newDay
                        );

                        Transaction::updateOrCreate(
                            [
                                'user_id' => $transaction->user_id,
                                'category_id' => $transaction->category_id,
                                'merchant' => $transaction->merchant,
                                'amount' => $transaction->amount,
                                'due_at' => $newDate,
                            ],
                            [
                                'type' => $transaction->type,
                                'payment_method' => $transaction->payment_method,
                                'notes' => null,
                                'is_recurring' => $transaction->is_recurring,
                                'status' => false,
                            ]
                        );

                        $count++;
                    }

                    Notification::make()
                        ->title('Next month prepared')
                        ->body("{$count} transactions copied to {$nextMonth->format('F Y')}.")
                        ->success()
                        ->send();
                }),
        ];
    }

    public function mount(): void
    {
        $this->month = now()->month;
        $this->year = now()->year;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            WeeklyCashFlowStats::class,
            BudgetStats::class,
        ];
    }
}
