<?php

namespace App\Filament\Actions;

use App\Notifications\TransactionCsvImportFailed;
use App\Services\TransactionCsvImporter;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification as LaravelNotification;
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
            // Modal
            ->modalHeading('Import Transactions')
            ->modalIcon('heroicon-o-arrow-up-tray')
            ->modalIconColor('primary')
            ->modalWidth(Width::ThreeExtraLarge)
            ->modalDescription(
                'Import transactions from your bank or credit card to quickly build your budget.'
            )
            ->modalContent(
                fn() => view('filament.modals.import-transactions-csv')
            )
            ->modalSubmitActionLabel('Import Transactions')
            ->modalCancelActionLabel('Cancel')
            ->stickyModalFooter()
            ->form([
                FileUpload::make('file')
                    ->label('CSV File')
                    ->validationAttribute('CSV file')
                    ->helperText(
                        '🔒 Your CSV is processed securely and removed from temporary storage after importing.'
                    )
                    ->disk('local')
                    ->directory('transaction-imports')
                    ->acceptedFileTypes([
                        'text/csv',
                        'text/plain',
                        'application/csv',
                        'text/comma-separated-values',
                        'application/vnd.ms-excel',
                        'application/octet-stream',
                    ])
                    ->rules([
                        'extensions:csv',
                    ])
                    ->required(),

                Select::make('account_type')
                    ->label('Import From')
                    ->options([
                        'auto' => 'Auto Detect',
                        'bank' => 'Bank / Checking Account',
                        'credit_card' => 'Credit Card',
                    ])
                    ->default('auto')
                    ->required(),

                Select::make('amount_convention')
                    ->label('How Are Purchases Shown?')
                    ->options([
                        'negative_expense' => 'Negative Amounts (-$50.00)',
                        'positive_expense' => 'Positive Amounts ($50.00)',
                    ])
                    ->default('negative_expense')
                    ->required()
                    ->helperText('Most bank statements show purchases as negative amounts.
                    Check your CSV file to see whether purchases are positive or negative amounts.'),
            ])
            ->action(function (array $data) {
                $path = Storage::disk('local')->path($data['file']);

                try {
                    $result = app(TransactionCsvImporter::class)->import(
                        userId: auth()->id(),
                        path: $path,
                        accountType: $data['account_type'],
                        amountConvention: $data['amount_convention'],
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
                    $user = auth()->user();

                    Log::error('Transaction CSV import failed.', [
                        'user_id' => $user->id,
                        'file' => $data['file'],
                        'path' => $path,
                        'exception' => $exception->getMessage(),
                        'exception_file' => $exception->getFile(),
                        'exception_line' => $exception->getLine(),
                    ]);

                    try {
                        LaravelNotification::route('mail', config('support.email'))
                            ->notify(
                                new TransactionCsvImportFailed(
                                    user: $user,
                                    exception: $exception,
                                    filePath: $path,
                                    originalFileName: basename($data['file']),
                                )
                            );
                    } catch (Throwable $mailException) {
                        Log::error('Unable to send CSV import failure notification.', [
                            'user_id' => $user->id,
                            'exception' => $mailException->getMessage(),
                        ]);
                    }

                    Notification::make()
                        ->title('Transaction import failed')
                        ->body(
                            'There was a problem importing the CSV file. ' .
                            'The issue has been sent to support for review.'
                        )
                        ->danger()
                        ->send();
                } finally {
                    Storage::disk('local')->delete($data['file']);
                }
            });
    }
}
