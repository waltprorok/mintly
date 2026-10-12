<?php

namespace App\Filament\Actions;

use App\Services\TransactionCsvImporter;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ImportTransactionsCsvAction
{
    public static function make(): Action
    {
        return Action::make('importTransactionsCsv')
            ->label('Import CSV')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('gray')
            ->modalHeading('Import Transactions')
            ->modalDescription(
                'Upload a CSV exported from your bank or credit card. We recommend importing a full month of transactions to create an accurate budget foundation. Once imported, review your transactions to set their categories and recurring frequencies.'
            )
            ->form([
                FileUpload::make('file')
                    ->label('CSV File')
                    ->disk('local')
                    ->directory('transaction-imports')
                    ->acceptedFileTypes([
                        'text/csv',
                        'text/plain',
                        'application/vnd.ms-excel',
                    ])
                    ->required(),
            ])
            ->action(function (array $data) {
                $path = storage_path('app/' . $data['file']);

                try {
                    $result = app(TransactionCsvImporter::class)->import(
                        userId: auth()->id(),
                        path: $path,
                    );

                    $notification = Notification::make()
                        ->title('Transaction import complete')
                        ->body(
                            "{$result['created']} created. " .
                            "{$result['duplicates']} duplicates skipped. " .
                            "{$result['transfers']} transfers skipped. " .
                            "{$result['unmatched']} unmatched need categorization."
                        );

                    if ($result['unmatched'] > 0) {
                        $notification->warning();
                    } else {
                        $notification->success();
                    }

                    $notification->send();
                } catch (Throwable $exception) {
                    Log::error('Transaction CSV import failed.', [
                        'user_id' => auth()->id(),
                        'file' => $data['file'],
                        'path' => $path,
                        'exception' => $exception->getMessage(),
                    ]);

                    Notification::make()
                        ->title('Transaction import failed')
                        ->body('There was a problem importing the CSV file. Please verify the file format and try again.')
                        ->danger()
                        ->send();
                } finally {
                    Storage::disk('local')->delete($data['file']);
                }
            });
    }
}
