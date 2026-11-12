<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\CategoryBudget;
use App\Models\Transaction;
use App\Services\CategoryBudgetService;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class BudgetTracker extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Budget Tracker';

    protected static ?string $title = 'Budget Tracker';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.budget-tracker';

    public string $selectedMonth = '';

    public function mount(): void
    {
        $this->selectedMonth = now()->format('Y-m');
    }

    protected function budgetMonth(): string
    {
        return Carbon::createFromFormat(
            '!Y-m',
            $this->selectedMonth
        )->startOfMonth()->toDateString();
    }

    public function updatedSelectedMonth(): void
    {
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn(): Builder => CategoryBudget::query()
                ->select('category_budgets.*')
                ->with('category')
                ->where('user_id', auth()->id())
                ->whereDate('month', $this->budgetMonth())
                ->addSelect([
                    'actual_spending' => Transaction::query()
                        ->selectRaw('COALESCE(SUM(amount), 0)')
                        ->whereColumn('transactions.category_id', 'category_budgets.category_id')
                        ->whereColumn('transactions.user_id', 'category_budgets.user_id')
                        ->where('type', 'expense')
                        ->whereBetween('due_at', [
                            Carbon::parse($this->budgetMonth())->startOfMonth(),
                            Carbon::parse($this->budgetMonth())->endOfMonth(),
                        ]),

                    'paid_spending' => Transaction::query()
                        ->selectRaw('COALESCE(SUM(amount), 0)')
                        ->whereColumn('transactions.category_id', 'category_budgets.category_id')
                        ->whereColumn('transactions.user_id', 'category_budgets.user_id')
                        ->where('type', 'expense')
                        ->where('is_paid', true)
                        ->whereBetween('due_at', [
                            Carbon::parse($this->budgetMonth())->startOfMonth(),
                            Carbon::parse($this->budgetMonth())->endOfMonth(),
                        ]),
                ])
            )
            ->heading(view('filament.tables.budget-tracker-header'))
            ->columns([
                TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Budget')
                    ->money('USD')
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->label('Total')
                            ->money('USD')
                    ),

                TextColumn::make('actual_spending')
                    ->label('Allocated')
                    ->money('USD')
                    ->summarize(
                        Summarizer::make()
                            ->label('Total')
                            ->using(function (QueryBuilder $query): float {
                                $categoryIds = (clone $query)
                                    ->select('category_id')
                                    ->pluck('category_id');

                                return (float)Transaction::query()
                                    ->where('user_id', auth()->id())
                                    ->whereIn('category_id', $categoryIds)
                                    ->where('type', 'expense')
//                                    ->where('is_paid', true)
                                    ->whereBetween('due_at', [
                                        Carbon::parse($this->budgetMonth())->startOfMonth(),
                                        Carbon::parse($this->budgetMonth())->endOfMonth(),
                                    ])
                                    ->sum('amount');
                            })
                            ->money('USD')
                    ),

                TextColumn::make('paid_spending')
                    ->label('Paid')
                    ->money('USD')
                    ->summarize(
                        Summarizer::make()
                            ->label('Total')
                            ->using(function (QueryBuilder $query): float {
                                $categoryIds = (clone $query)
                                    ->select('category_id')
                                    ->pluck('category_id');

                                return (float)Transaction::query()
                                    ->where('user_id', auth()->id())
                                    ->whereIn('category_id', $categoryIds)
                                    ->where('type', 'expense')
                                    ->where('is_paid', true)
                                    ->whereBetween('due_at', [
                                        Carbon::parse($this->budgetMonth())->startOfMonth(),
                                        Carbon::parse($this->budgetMonth())->endOfMonth(),
                                    ])
                                    ->sum('amount');
                            })
                            ->money('USD')
                    ),

                TextColumn::make('remaining')
                    ->label('Remaining')
                    ->state(fn(CategoryBudget $record): float => (float)$record->amount - (float)$record->paid_spending
                    )
                    ->money('USD')
                    ->color(fn($state) => $state < 0 ? 'danger' : 'success')
                    ->summarize(
                        Summarizer::make()
                            ->label('Total')
                            ->using(function (QueryBuilder $query): float {
                                $budgets = (clone $query)
                                    ->select(['category_id', 'amount'])
                                    ->get();

                                $planned = (float)$budgets->sum('amount');

                                $actual = (float)Transaction::query()
                                    ->where('user_id', auth()->id())
                                    ->whereIn('category_id', $budgets->pluck('category_id'))
                                    ->where('type', 'expense')
                                    ->where('is_paid', true)
                                    ->whereBetween('due_at', [
                                        Carbon::parse($this->budgetMonth())->startOfMonth(),
                                        Carbon::parse($this->budgetMonth())->endOfMonth(),
                                    ])
                                    ->sum('amount');

                                return $planned - $actual;
                            })
                            ->money('USD')
                    ),

                ViewColumn::make('progress')
                    ->label('Progress')
                    ->view('filament.tables.columns.budget-progress'),
            ])
            ->headerActions([
                Action::make('generateBudgets')
                    ->label('Generate Budgets')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('Generate Monthly Budgets')
                    ->modalDescription('Generate budgets using the previous month’s transactions. Existing budgets will not be overwritten.')
                    ->action(function (CategoryBudgetService $service): void {
                        $targetMonth = Carbon::createFromFormat(
                            '!Y-m',
                            $this->selectedMonth
                        )->startOfMonth();

                        $sourceMonth = $targetMonth->copy()->subMonth();

                        $created = $service->generate(
                            auth()->id(),
                            $sourceMonth,
                            $targetMonth
                        );

                        Notification::make()
                            ->title("{$created} category budgets created")
                            ->success()
                            ->send();

                        $this->resetTable();
                    }),

                Action::make('copyBudgets')
                    ->label('Copy to Next Month')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->visible(function (): bool {
                        $sourceMonth = Carbon::createFromFormat(
                            '!Y-m',
                            $this->selectedMonth
                        )->startOfMonth();

                        return CategoryBudget::query()
                            ->where('user_id', auth()->id())
                            ->whereDate('month', $sourceMonth->toDateString())
                            ->exists();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Copy Budgets to Next Month')
                    ->modalDescription('Copy the selected month’s budgets to the next month. Existing budgets will not be overwritten.')
                    ->action(function (CategoryBudgetService $service): void {
                        $sourceMonth = Carbon::createFromFormat(
                            '!Y-m',
                            $this->selectedMonth
                        )->startOfMonth();

                        $targetMonth = $sourceMonth->copy()->addMonth();

                        $created = $service->copyToNextMonth(
                            auth()->id(),
                            $sourceMonth,
                            $targetMonth
                        );

                        Notification::make()
                            ->title("{$created} category budgets copied")
                            ->success()
                            ->send();

                        $this->resetTable();
                    }),

                CreateAction::make()
                    ->label('Add Budget')
                    ->model(CategoryBudget::class)
                    ->schema($this->budgetFormSchema())
                    ->mutateDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();
                        $data['month'] = $this->budgetMonth();

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->icon('heroicon-o-pencil-square')
                    ->iconButton()
                    ->tooltip('Edit Budget')
                    ->schema($this->budgetFormSchema())
                    ->mutateDataUsing(function (array $data): array {
                        unset($data['category_id']);

                        return $data;
                    }),

                DeleteAction::make()
                    ->icon('heroicon-o-trash')
                    ->iconButton()
                    ->tooltip('Delete Budget'),
            ], position: RecordActionsPosition::AfterColumns)
            ->recordActionsColumnLabel('Actions')
            ->defaultSort(function (Builder $query): Builder {
                return $query->orderBy(
                    Category::query()
                        ->select('name')
                        ->whereColumn('categories.id', 'category_budgets.category_id')
                );
            });
    }

    protected function monthOptions(): array
    {
        $months = [];

        for ($i = -5; $i <= 6; $i++) {
            $month = now()->startOfMonth()->addMonths($i);

            $months[$month->format('Y-m')] = $month->format('F Y');
        }

        return $months;
    }

    protected function budgetFormSchema(): array
    {
        return [
            Select::make('category_id')
                ->label('Category')
                ->options(fn() => Category::query()
                    ->where('user_id', auth()->id())
                    ->where('type', 'expense')
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->all()
                )
                ->required(),

            TextInput::make('amount')
                ->label('Monthly Budget')
                ->numeric()
                ->prefix('$')
                ->minValue(0)
                ->required(),
        ];
    }
}
