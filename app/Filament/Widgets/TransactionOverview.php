<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Transactions\Pages\ListTransactions;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TransactionOverview extends StatsOverviewWidget
{
    use InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return ListTransactions::class;
    }

    protected function getStats(): array
    {
        $query = $this->getPageTableQuery();

        $income = (clone $query)
            ->where('type', 'income')
            ->sum('amount');

        $expenses = (clone $query)
            ->where('type', 'expense')
            ->sum('amount');

        $net = $income - $expenses;

        return [
            Stat::make('Income', '$' . number_format($income, 2))
                ->icon('heroicon-o-arrow-trending-up')
                ->color('success'),

            Stat::make('Expenses', '$' . number_format($expenses, 2))
                ->icon('heroicon-o-arrow-trending-down')
                ->color('danger'),

            Stat::make('Net', '$' . number_format($net, 2))
                ->icon('heroicon-o-banknotes')
                ->color($net >= 0 ? 'success' : 'danger'),
        ];
    }
}
