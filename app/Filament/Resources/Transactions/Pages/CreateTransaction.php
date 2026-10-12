<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Actions\ImportTransactionsCsvAction;
use App\Filament\Resources\Transactions\TransactionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportTransactionsCsvAction::make(),
        ];
    }
}
