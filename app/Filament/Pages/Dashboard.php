<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
//    protected function getHeaderWidgets(): array
//    {
//        return [
//            MonthlyIncome::class,
//            MonthlyExpenses::class,
//            MonthlyNet::class,
//            IncomeExpenseChart::class,
//            ExpenseCategoryChart::class,
//            UpcomingBills::class,
//        ];
//    }

    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'lg' => 2,
        ];
    }
}
