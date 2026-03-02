<?php

namespace App\Filament\Resources\Transactions;

use App\Models\Transaction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-banknotes';
    protected static string|null|\UnitEnum $navigationGroup = 'Mintly';

    public static function getEloquentQuery(): Builder
    {
        // SaaS protection
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id());
    }

    /* -------------------------------------------------
     | FORM (v4 syntax)
     |-------------------------------------------------*/
    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            Hidden::make('user_id')
                ->default(fn() => auth()->id())
                ->required(),

            Select::make('type')
                ->options([
                    'income' => 'Income',
                    'expense' => 'Expense',
                ])
                ->required(),

            Select::make('category_id')
                ->relationship('category', 'name')
                ->searchable()
                ->preload()
                ->required(),

            TextInput::make('amount')
                ->numeric()
                ->required()
                ->prefix('$'),

            DateTimePicker::make('due_at')
                ->seconds(false)
                ->nullable(),

            TextInput::make('merchant')
                ->nullable(),

            Select::make('payment_method')
                ->options([
                    'credit_card' => 'Credit Card',
                    'bank' => 'Bank',
                    'cash' => 'Cash',
                    'other' => 'Other',
                ])
                ->nullable(),

            Toggle::make('status')
                ->label('Paid')
                ->default(false),

            Toggle::make('is_recurring')
                ->default(false),

            Textarea::make('notes')
                ->rows(4)
                ->nullable(),
        ]);
    }

    /* -------------------------------------------------
     | TABLE
     |-------------------------------------------------*/
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('due_at')
                    ->dateTime('M j, Y')
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

                TextColumn::make('category.name')
                    ->sortable(),

                TextColumn::make('merchant')
                    ->sortable(),

                TextColumn::make('amount')
                    ->money('USD')
                    ->sortable(),

                IconColumn::make('is_recurring')
                    ->label('Recurring')
                    ->boolean(),

                ToggleColumn::make('status')
                    ->label('Paid')
                    ->sortable()
                    ->onColor('success')   // green when true
                    ->offColor('gray') ,    // gray when false
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'income' => 'Income',
                        'expense' => 'Expense',
                    ]),
            ])
            ->defaultSort('due_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
