<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Actions\DownloadTransactionsPdfAction;
use App\Filament\Actions\ImportTransactionsCsvAction;
use App\Filament\Resources\Transactions\TransactionResource;
use App\Filament\Widgets\TransactionOverview;
use Filament\Actions\CreateAction;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;

class ListTransactions extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportTransactionsCsvAction::make(),

            DownloadTransactionsPdfAction::make(
                fn () => $this->getFilteredTableQuery()
            ),

            CreateAction::make()->label('New transaction'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TransactionOverview::class,
        ];
    }
}
