<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use App\Filament\Widgets\BudgetStats;
use App\Filament\Widgets\WeeklyCashFlowStats;
use App\Models\Transaction;
use App\Services\RecurringTransactionService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class MonthlyBudget extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?int $navigationSort = 1;
    protected string $view = 'filament.pages.monthly-budget';
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-table-cells';
    protected static ?string $slug = 'budget';
    protected static bool $shouldRegisterNavigation = true;
    protected static ?string $navigationLabel = 'Budget Planner';

    protected static ?string $title = 'Monthly Budget';

    public array $filters = [];

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaction::query()
                    ->with('category')
                    ->where('user_id', auth()->id())
                    ->whereIn('type', ['income', 'expense'])
                    ->orderByRaw("type = 'expense'")
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
                    ->label('Merchant')
                    ->default(fn($record) => $record->category->name),

                TextColumn::make('amount')
                    ->money('USD')
                    ->sortable()
                    ->summarize([
                        Sum::make()
                            ->html()
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

                TextColumn::make('payment_method')
                    ->label('Payment')
                    ->formatStateUsing(fn($state) => str($state)->replace('_', ' ')->title()),

                ...collect(range(1, $this->getWeeksInMonth()))->map(
                    fn($week) => TextColumn::make("week{$week}")
                        ->label("Week {$week}")
                        ->money('USD')
                        ->getStateUsing(function ($record) use ($week) {
                            if (! $record->due_at) {
                                return null;
                            }

                            $weekOfMonth = $this->getWeekOfSelectedMonth($record->due_at);

                            return $weekOfMonth === $week ? $record->amount : null;
                        })
                )->all(),

                ToggleColumn::make('is_paid')
                    ->label('Paid')
                    ->onColor('success')
                    ->offColor('gray'),
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
                            ->default(now()->month)
                            ->reactive()
                            ->afterStateUpdated(fn($state) => $this->filters['month'] = $state),

                        Select::make('year')
                            ->label('Year')
                            ->options(function () {
                                $oldest = Transaction::min('due_at');

                                $oldestYear = $oldest
                                    ? Carbon::parse($oldest)->year
                                    : now()->year;

                                $lastYear = now()->addMonth()->year;

                                return collect(range($oldestYear, $lastYear))
                                    ->reverse()
                                    ->mapWithKeys(fn($year) => [$year => $year])
                                    ->toArray();
                            })
                            ->default(now()->year)
                            ->reactive()
                            ->afterStateUpdated(fn($state) => $this->filters['year'] = $state),
                    ])
                    ->query(function ($query, array $data) {
                        $this->dispatch(
                            'updateBudgetStats',
                            month: $data['month'] ?? now()->month,
                            year: $data['year'] ?? now()->year
                        );

                        return $query
                            ->whereMonth('due_at', $data['month'] ?? now()->month)
                            ->whereYear('due_at', $data['year'] ?? now()->year);
                    })
                    ->indicateUsing(function (array $data) {
                        $month = $data['month'] ?? now()->month;
                        $year = $data['year'] ?? now()->year;

                        return Carbon::create($year, $month)->format('F Y');
                    }),
            ])
            ->recordAction('editTransaction')
            ->recordUrl(null)
            ->actions([
                Action::make('editTransaction')
                    ->icon('heroicon-o-pencil-square')
                    ->label(' ')
                    ->color('blue')
                    ->modalHeading('Edit Transaction')
                    ->modalWidth('lg')
                    ->form(
                        TransactionResource::form(
                            app(Schema::class)
                        )->getComponents()
                    )
                    ->fillForm(fn($record) => $record->toArray())
                    ->action(function ($record, array $data) {
                        $record->update($data);
                    })
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('roll_forward')
                ->label('Prepare Next Period')
                ->icon('heroicon-o-arrow-right-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Prepare Next Period')
                ->modalDescription('Recurring transactions will be carried forward based on their frequency.')
                ->modalSubmitActionLabel('Prepare')
                ->action(function () {
                    $count = app(RecurringTransactionService::class)
                        ->run(auth()->id(), true);

                    Notification::make()
                        ->title('Next month prepared')
                        ->body("{$count} transactions created.")
                        ->success()
                        ->send();
                }),
        ];
    }

    public function mount(): void
    {
        $this->filters = [
            'month' => now()->month,
            'year' => now()->year,
        ];
    }

    protected function getSelectedMonth(): int
    {
        return $this->filters['month'] ?? now()->month;
    }

    protected function getSelectedYear(): int
    {
        return $this->filters['year'] ?? now()->year;
    }

    protected function getWeekOfSelectedMonth($date): int
    {
        return (int)ceil(Carbon::parse($date)->day / 7);
    }

    protected function getWeeksInMonth(): int
    {
        $days = Carbon::create(
            $this->getSelectedYear(),
            $this->getSelectedMonth()
        )->daysInMonth;

        return (int)ceil($days / 7);
    }

    public function getSubheading(): ?string
    {
        return Carbon::create(
            $this->getSelectedYear(),
            $this->getSelectedMonth()
        )->format('F Y');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            BudgetStats::class,
            WeeklyCashFlowStats::class,
        ];
    }
}
