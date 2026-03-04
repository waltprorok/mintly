<?php

namespace App\Filament\Pages;

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
                    ->whereMonth('due_at', $this->month)
                    ->whereYear('due_at', $this->year)
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
                    ->alignRight()
                    ->default(fn($record) => $record->category->name),

                TextColumn::make('amount')
                    ->money('USD')
                    ->alignRight()
                    ->sortable()
                    ->summarize([
                        // This one is OK to show per category (category expense totals)
//                        Sum::make()
//                            ->money('USD')
//                            ->label('Category Total'),
//
//                        // Footer-only
//                        Sum::make()
//                            ->money('USD')
//                            ->label('Total Income')
//                            ->query(fn ($query) => $query->where('transactions.type', 'income')),
//
                        Sum::make()
                            ->money('USD')
                            ->label('Total Expenses')
                            ->query(fn($query) => $query->where('transactions.type', 'expense')),
                    ]),
                ...collect(range(1, 4))->map(
                    fn($week) => TextColumn::make("week{$week}")
                        ->label("Week {$week}")
                        ->alignRight()
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
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('period')
                ->label('Change Period')
                ->modalWidth('sm')
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
                        ->default($this->month)
                        ->required(),

                    Select::make('year')
                        ->label('Year')
                        ->options(
                            collect(range(now()->year - 1, now()->year + 1))
                                ->mapWithKeys(fn($y) => [$y => $y])
                        )
                        ->default($this->year)
                        ->required(),

                ])
                ->action(function (array $data) {

                    $this->month = $data['month'];
                    $this->year = $data['year'];

                    $this->loadIncome();

                    $this->resetTable();
                }),

            Action::make('roll_forward')
                ->label('Prepare Next Month')
                ->icon('heroicon-o-arrow-right-circle')
                ->color('success')

                ->requiresConfirmation()

                ->modalHeading('Prepare Next Month')
                ->modalDescription('This will copy all transactions from the current month into next month.')

                ->modalSubmitActionLabel('Copy Transactions')

                ->action(function () {
                    $currentMonth = Carbon::create($this->year, $this->month);
                    $nextMonth = $currentMonth->copy()->addMonth();

                    $lastDay = $nextMonth->copy()->endOfMonth()->day;

                    $transactions = Transaction::query()
                        ->where('user_id', auth()->id())
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
                                'notes' => $transaction->notes,
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

        $this->loadIncome();
    }

    protected function loadIncome(): void
    {
        $this->income = Transaction::query()
            ->where('user_id', auth()->id())
            ->where('type', 'income')
            ->whereMonth('due_at', $this->month)
            ->whereYear('due_at', $this->year)
            ->sum('amount');
    }
}
