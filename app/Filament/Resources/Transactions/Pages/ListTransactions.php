<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Actions\CheckTransactionDuplicatesAction;
use App\Filament\Actions\DownloadTransactionsPdfAction;
use App\Filament\Actions\ImportTransactionsCsvAction;
use App\Filament\Resources\Transactions\TransactionResource;
use App\Filament\Widgets\TransactionOverview;
use Carbon\Carbon;
use Filament\Actions\CreateAction;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;

class ListTransactions extends ListRecords
{
    use ExposesTableToWidgets;

    public array $duplicateTransactionIds = [];

    protected static string $resource = TransactionResource::class;

    protected function getTableQueryStringIdentifier(): ?string
    {
        return null;
    }

    protected function getHeaderActions(): array
    {
        return [
            ImportTransactionsCsvAction::make(),
            CheckTransactionDuplicatesAction::make(),
            DownloadTransactionsPdfAction::make(
                fn () => $this->getFilteredTableQuery()
            ),

            CreateAction::make()->label('New transaction'),
        ];
    }

    public function getSubheading(): ?string
    {
        $month = $this->tableFilters['month']['value'] ?? null;
        $year = $this->tableFilters['year']['value'] ?? null;

        if ($month && $year) {
            return Carbon::create(
                (int) $year,
                (int) $month,
                1
            )->format('F Y');
        }

        if ($year) {
            return (string) $year;
        }

        if ($month) {
            return Carbon::create(
                now()->year,
                (int) $month,
                1
            )->format('F');
        }

        return 'All Transactions';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TransactionOverview::class,
        ];
    }

    public function getSelectedMonth(): ?int
    {
        $month = $this->tableFilters['month']['value'] ?? null;

        return filled($month) ? (int) $month : null;
    }

    public function getSelectedYear(): ?int
    {
        $year = $this->tableFilters['year']['value'] ?? null;

        return filled($year) ? (int) $year : null;
    }
}
