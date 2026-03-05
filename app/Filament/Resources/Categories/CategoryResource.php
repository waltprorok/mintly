<?php

namespace App\Filament\Resources\Categories;

use App\Models\Category;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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
//    protected static string|null|\UnitEnum $navigationGroup = 'Mintly';
    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where(function (Builder $query) {
                $query->where('user_id', auth()->id())
                    ->orWhereNull('user_id');
            });
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
                    'both' => 'Both',
                ])
                ->default('both')
                ->required(),

            Select::make('spend_classification')
                ->label('Needs vs Wants')
                ->options([
                    'discretionary' => 'Discretionary (Wants)',
                    'non_discretionary' => 'Non-Discretionary (Needs)',
                    'unknown' => 'Unknown',
                ])
                ->default('unknown')
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
                    ->color('gray')
                    ->formatStateUsing(function ($state) {
                        return str($state)
                            ->replace('_', '-')
                            ->lower()
                            ->title();
                    })
                    ->sortable(),

//                IconColumn::make('user_id')
//                    ->label('Global')
//                    ->boolean()
//                    ->state(fn (Category $record) => $record->user_id === null),
            ])
            ->persistFiltersInSession()
            ->headerActions([
                Action::make('install_defaults')
                    ->label('Create Default Categories')
                    ->icon('heroicon-o-sparkles')
                    ->color('info')
                    ->form([
                        CheckboxList::make('categories')
                            ->label('Choose Categories')
                            ->options([
                                'Housing' => 'Housing',
                                'Rent' => 'Rent',
                                'Utilities' => 'Utilities',
                                'Groceries' => 'Groceries',
                                'Transportation' => 'Transportation',
                                'Insurance' => 'Insurance',
                                'Healthcare' => 'Healthcare',

                                'Subscriptions' => 'Subscriptions',
                                'Dining' => 'Dining',
                                'Entertainment' => 'Entertainment',
                                'Shopping' => 'Shopping',
                                'Travel' => 'Travel',

                                'Savings' => 'Savings',
                                'Investments' => 'Investments',

                                'Income' => 'Income',
                                'Salary' => 'Salary',
                                'Bonus' => 'Bonus',
                                'Side Hustle' => 'Side Hustle',
                            ])
                            ->columns(3)
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $map = [
                            'Housing' => ['expense','non_discretionary'],
                            'Rent' => ['expense','non_discretionary'],
                            'Utilities' => ['expense','non_discretionary'],
                            'Groceries' => ['expense','non_discretionary'],
                            'Transportation' => ['expense','non_discretionary'],
                            'Insurance' => ['expense','non_discretionary'],
                            'Healthcare' => ['expense','non_discretionary'],

                            'Subscriptions' => ['expense','discretionary'],
                            'Dining' => ['expense','discretionary'],
                            'Entertainment' => ['expense','discretionary'],
                            'Shopping' => ['expense','discretionary'],
                            'Travel' => ['expense','discretionary'],

                            'Savings' => ['both','non_discretionary'],
                            'Investments' => ['both','non_discretionary'],

                            'Income' => ['income','unknown'],
                            'Salary' => ['income','unknown'],
                            'Bonus' => ['income','unknown'],
                            'Side Hustle' => ['income','unknown'],
                        ];

                        foreach ($data['categories'] as $name) {
                            [$type,$classification] = $map[$name];

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
                    }),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'income' => 'Income',
                        'expense' => 'Expense',
                        'both' => 'Both',
                    ]),

                SelectFilter::make('spend_classification')
                    ->options([
                        'discretionary' => 'Discretionary',
                        'non_discretionary' => 'Non-Discretionary',
                        'unknown' => 'Unknown',
                    ]),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
