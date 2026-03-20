<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CategoryBreakdownChart;
use Filament\Pages\Page;

class Reports extends Page
{
    protected static string|null|\BackedEnum $navigationIcon= 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Reports';

    protected static ?string $title = 'Reports';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.reports';

    protected function getHeaderWidgets(): array
    {
        return [
            CategoryBreakdownChart::class,
        ];
    }
}
