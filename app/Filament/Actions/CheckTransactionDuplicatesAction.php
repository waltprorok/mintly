<?php

namespace App\Filament\Actions;

use App\Services\TransactionDuplicateService;
use Filament\Actions\Action;
use Livewire\Component;

class CheckTransactionDuplicatesAction
{
    public static function make(): Action
    {
        return Action::make('checkTransactionDuplicates')
            ->label('Check Duplicates')
            ->icon('heroicon-o-document-duplicate')
            ->color('gray')
            ->action(function (Component $livewire) {

                // Reset previous duplicate results.
                $livewire->duplicateTransactionIds = [];

                // Disable the duplicate filter before querying.
                $livewire->tableFilters['potential_duplicates'] = [
                    'isActive' => false,
                ];

                // Get transactions matching current table filters.
                $query = $livewire->getFilteredTableQuery();

                // Find duplicates.
                $duplicates = app(TransactionDuplicateService::class)
                    ->find($query);

                // Collect duplicate IDs.
                $duplicateIds = $duplicates
                    ->flatMap(fn (array $pair) => [
                        $pair['original']->id,
                        $pair['duplicate']->id,
                    ])
                    ->unique()
                    ->values()
                    ->all();

                // Store duplicate IDs.
                $livewire->duplicateTransactionIds = $duplicateIds;

                // Activate duplicate filter.
                $livewire->tableFilters['potential_duplicates'] = [
                    'isActive' => true,
                ];

                $livewire->resetPage();
            });
    }
}
