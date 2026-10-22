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
                // Clear previous duplicate results before running a new check.
                $livewire->tableFilters['potential_duplicates'] = [
                    'isActive' => false,
                ];

                $duplicates = app(TransactionDuplicateService::class)
                    ->find($livewire->getFilteredTableQuery());

                $duplicateIds = $duplicates
                    ->flatMap(fn(array $pair) => [
                        $pair['original']->id,
                        $pair['duplicate']->id,
                    ])
                    ->unique()
                    ->values()
                    ->all();

                $livewire->duplicateTransactionIds = $duplicateIds;

                $livewire->tableFilters['potential_duplicates'] = [
                    'isActive' => true,
                ];

                $livewire->resetPage();
            });
    }
}
