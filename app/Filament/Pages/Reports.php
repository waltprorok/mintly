<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CategoryBreakdownChart;
use App\Filament\Widgets\CategoryBreakdownTable;
use App\Filament\Widgets\IncomeVsExpenseChart;
use App\Filament\Widgets\IncomeVsExpenseTable;
use App\Filament\Widgets\TransactionsOverTimeChart;
use App\Filament\Widgets\WantsVsNeedsChart;
use App\Filament\Widgets\WantsVsNeedsTable;
use Filament\Pages\Page;

class Reports extends Page
{
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Reports';

    protected static ?string $title = 'Reports';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.reports';

    protected function getHeaderWidgets(): array
    {
        return [
            IncomeVsExpenseChart::class,
            IncomeVsExpenseTable::class,
            CategoryBreakdownChart::class,
            CategoryBreakdownTable::class,
            WantsVsNeedsChart::class,
            WantsVsNeedsTable::class,
            TransactionsOverTimeChart::class,
        ];
    }

}
