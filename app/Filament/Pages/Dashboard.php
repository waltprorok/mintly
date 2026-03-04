<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\IncomeExpenseChart;
use App\Filament\Widgets\MonthlyExpenses;
use App\Filament\Widgets\MonthlyIncome;
use App\Filament\Widgets\MonthlyNet;
use App\Filament\Widgets\UpcomingBills;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected function getHeaderWidgets(): array
    {
        return [
            MonthlyIncome::class,
            MonthlyExpenses::class,
            MonthlyNet::class,
            UpcomingBills::class,
            IncomeExpenseChart::class,
        ];
    }

    public function getColumns(): int | array
    {
        return [
            'default' => 1,
            'lg' => 2,
        ];
    }
}
