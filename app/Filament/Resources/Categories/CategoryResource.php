<?php

namespace App\Filament\Resources\Categories;

use App\Filament\Resources\Transactions\TransactionResource;
use App\Models\Category;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-tag';
    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where(function (Builder $query) {
                $query->where('user_id', auth()->id())
                    ->orWhereNull('user_id');
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }

    public static function canDelete($record): bool
    {
        return ! $record->transactions()->exists();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Hidden::make('user_id')
                ->default(fn() => auth()->id()),

            TextInput::make('name')
                ->required()
                ->placeholder('Subscriptions')
                ->maxLength(255),

            Select::make('type')
                ->options([
                    'income' => 'Income',
                    'expense' => 'Expense',
//                    'both' => 'Both',
                ])
//                ->default('expense')
                ->required(),

            Select::make('spend_classification')
                ->label('Needs vs Wants')
                ->options([
                    'discretionary' => 'Discretionary (Wants)',
                    'non_discretionary' => 'Non-Discretionary (Needs)',
                    'unknown' => 'Unknown',
                ])
//                ->default('discretionary')
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn($state) => ucfirst(strtolower($state)))
                    ->color(fn($state) => match (strtolower($state)) {
                        'income' => 'success', // green
                        'expense' => 'info', // blue
                        default => 'gray',

                    })
                    ->sortable(),

                TextColumn::make('spend_classification')
                    ->label('Classification')
                    ->badge()
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->type === 'income') {
                            return '--';
                        }

                        return match (strtolower($state ?? '')) {
                            'non_discretionary' => 'Non-Discretionary (Needs)',
                            'discretionary' => 'Discretionary (Wants)',
                            default => 'Unclassified',
                        };
                    })
                    ->color(function ($state, $record) {
                        if ($record->type === 'income') {
                            return 'gray';
                        }

                        return match (strtolower($state ?? '')) {
                            'non_discretionary' => 'primary',
                            'discretionary' => 'warning',
                            default => 'gray',
                        };
                    })
                    ->sortable(),

//                IconColumn::make('user_id')
//                    ->label('Global')
//                    ->boolean()
//                    ->state(fn (Category $record) => $record->user_id === null),
            ])
            ->persistFiltersInSession()
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'income' => 'Income',
                        'expense' => 'Expense',
//                        'both' => 'Both',
                    ]),

                SelectFilter::make('spend_classification')
                    ->options([
                        'discretionary' => 'Discretionary',
                        'non_discretionary' => 'Non-Discretionary',
                        'unknown' => 'Unknown',
                    ]),
            ])
            ->actions([
                DeleteAction::make()
                    ->label('')
                    ->icon('heroicon-o-trash')
                    ->visible(fn($record) => ! $record->transactions()->exists())
                    ->before(function ($record) {
                        if ($record->transactions()->exists()) {
                            Notification::make()
                                ->title('Cannot delete category')
                                ->body('This category has transactions. Reassign or delete them first.')
                                ->danger()
                                ->send();

                            return false;
                        }
                    }),
            ])
            ->defaultSort('name');
    }

    public static function installDefaultsAction(): Action
    {
        return Action::make('install_defaults')
            ->label('Create Default Categories')
            ->icon('heroicon-o-sparkles')
            ->color('info')
            ->modalHeading('Create Default Categories')
            ->schema([
                CheckboxList::make('categories')
                    ->label('Choose Categories')
                    ->options([
                        // Income
                        'Salary' => 'Salary',
                        'Paycheck' => 'Paycheck',
                        'Bonus' => 'Bonus',
                        'Side Hustle' => 'Side Hustle',
                        'Second Job' => 'Second Job',
                        // Housing
                        'House' => 'House',
                        'Mortgage' => 'Mortgage',
                        'Rent' => 'Rent',
                        'Property Taxes' => 'Property Taxes',
                        'HOA' => 'HOA',
                        'Utilities' => 'Utilities',
                        'Internet' => 'Internet',
                        'Phone' => 'Phone',
                        'Home Maintenance' => 'Home Maintenance',
                        // Food
                        'Groceries' => 'Groceries',
                        'Dining' => 'Dining',
                        // Transportation
                        'Car Payment' => 'Car Payment',
                        'Gas' => 'Gas',
                        'Insurance' => 'Insurance',
                        'Car Maintenance' => 'Car Maintenance',
                        'Parking & Tolls' => 'Parking & Tolls',
                        'Transportation' => 'Transportation',
                        // Health
                        'Healthcare' => 'Healthcare',
                        'Prescriptions' => 'Prescriptions',
                        'Gym' => 'Gym',
                        'Personal Care' => 'Personal Care',
                        // Financial
                        'Debt Payments' => 'Debt Payments',
                        'Student Loan' => 'Student Loan',
                        'Taxes' => 'Taxes',
                        'Fees & Charges' => 'Fees & Charges',
                        // Lifestyle
                        'Subscriptions' => 'Subscriptions',
                        'Entertainment' => 'Entertainment',
                        'Clothing' => 'Clothing',
                        'Shopping' => 'Shopping',
                        'Amazon Purchases' => 'Amazon Purchases',
                        'Household' => 'Household',
                        'Travel' => 'Travel',
                        'Gifts & Donations' => 'Gifts & Donations',
                        'Pets' => 'Pets',
                        'Hobbies' => 'Hobbies',
                        'Video Games' => 'Video Games',
                        // Family
                        'Childcare' => 'Childcare',
                        'Education' => 'Education',
                        // Catch-all
                        'One Time Purchase' => 'One Time Purchase',
                        'Miscellaneous' => 'Miscellaneous',
                    ])
                    ->disableOptionWhen(function (string $value): bool {
                        return Category::query()
                            ->where('user_id', auth()->id())
                            ->where('name', $value)
                            ->exists();
                    })
                    ->columns(4)
                    ->required(),
            ])
            ->action(function (array $data) {
                $map = [
                    // Income
                    'Salary' => ['income', 'unknown'],
                    'Paycheck' => ['income', 'unknown'],
                    'Bonus' => ['income', 'unknown'],
                    'Side Hustle' => ['income', 'unknown'],
                    'Second Job' => ['income', 'unknown'],
                    // Housing
                    'House' => ['expense', 'non_discretionary'],
                    'Mortgage' => ['expense', 'non_discretionary'],
                    'Rent' => ['expense', 'non_discretionary'],
                    'Property Taxes' => ['expense', 'non_discretionary'],
                    'HOA' => ['expense', 'non_discretionary'],
                    'Utilities' => ['expense', 'non_discretionary'],
                    'Internet' => ['expense', 'non_discretionary'],
                    'Phone' => ['expense', 'non_discretionary'],
                    'Home Maintenance' => ['expense', 'non_discretionary'],
                    // Food
                    'Groceries' => ['expense', 'non_discretionary'],
                    'Dining' => ['expense', 'discretionary'],
                    // Transportation
                    'Car Payment' => ['expense', 'non_discretionary'],
                    'Gas' => ['expense', 'non_discretionary'],
                    'Insurance' => ['expense', 'non_discretionary'],
                    'Car Maintenance' => ['expense', 'non_discretionary'],
                    'Parking & Tolls' => ['expense', 'non_discretionary'],
                    'Transportation' => ['expense', 'non_discretionary'],
                    // Health
                    'Healthcare' => ['expense', 'non_discretionary'],
                    'Prescriptions' => ['expense', 'non_discretionary'],
                    'Gym' => ['expense', 'discretionary'],
                    'Personal Care' => ['expense', 'discretionary'],
                    // Financial
                    'Debt Payments' => ['expense', 'non_discretionary'],
                    'Student Loan' => ['expense', 'non_discretionary'],
                    'Taxes' => ['expense', 'non_discretionary'],
                    'Fees & Charges' => ['expense', 'non_discretionary'],
                    // Lifestyle
                    'Subscriptions' => ['expense', 'discretionary'],
                    'Entertainment' => ['expense', 'discretionary'],
                    'Clothing' => ['expense', 'discretionary'],
                    'Shopping' => ['expense', 'discretionary'],
                    'Amazon Purchases' => ['expense', 'discretionary'],
                    'Household' => ['expense', 'discretionary'],
                    'Travel' => ['expense', 'discretionary'],
                    'Gifts & Donations' => ['expense', 'discretionary'],
                    'Pets' => ['expense', 'discretionary'],
                    'Hobbies' => ['expense', 'discretionary'],
                    'Video Games' => ['expense', 'discretionary'],
                    // Family
                    'Childcare' => ['expense', 'non_discretionary'],
                    'Education' => ['expense', 'non_discretionary'],
                    // Catch-all
                    'One Time Purchase' => ['expense', 'discretionary'],
                    'Miscellaneous' => ['expense', 'unknown'],
                ];

                foreach ($data['categories'] as $name) {
                    if (! isset($map[$name])) {
                        continue;
                    }

                    [$type, $classification] = $map[$name];

                    Category::firstOrCreate(
                        [
                            'user_id' => auth()->id(),
                            'name' => $name,
                        ],
                        [
                            'type' => $type,
                            'spend_classification' => $classification,
                        ]
                    );
                }

                Notification::make()
                    ->title('Categories created')
                    ->body('Your selected categories are ready. You can now add transactions or continue managing your categories.')
                    ->success()
                    ->actions([
                        Action::make('addTransaction')
                            ->label('Add Transaction')
                            ->button()
                            ->url(
                                TransactionResource::getUrl('create')
                            ),
                    ])
                    ->send();
            });
    }
}
