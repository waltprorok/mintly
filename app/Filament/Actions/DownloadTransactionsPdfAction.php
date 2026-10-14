<?php

namespace App\Filament\Actions;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Closure;
use Filament\Actions\Action;

class DownloadTransactionsPdfAction
{
    public static function make(Closure $query): Action
    {
        return Action::make('downloadPdf')
            ->label('Download PDF')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('gray')
            ->action(function () use ($query) {
                $transactions = (clone $query())
                    ->with('category')
                    ->orderBy('due_at')
                    ->get();

                $income = $transactions->where('type', 'income');

                $expenses = $transactions->where('type', 'expense');

                $incomeTotal = $income->sum('amount');
                $expenseTotal = $expenses->sum('amount');
                $net = $incomeTotal - $expenseTotal;

                $expensesByCategory = $expenses->groupBy(
                    fn ($transaction) =>
                        $transaction->category?->name ?? 'Uncategorized'
                );

                $from = $transactions->min('due_at');
                $until = $transactions->max('due_at');

                $periodLabel = $from && $until
                    ? Carbon::parse($from)->format('M j, Y')
                    . ' - '
                    . Carbon::parse($until)->format('M j, Y')
                    : 'No transactions';

                $pdf = Pdf::loadView('pdf.monthly-budget', [
                    'title' => 'Transaction Report',
                    'periodLabel' => $periodLabel,
                    'income' => $income,
                    'expensesByCategory' => $expensesByCategory,
                    'incomeTotal' => $incomeTotal,
                    'expenseTotal' => $expenseTotal,
                    'net' => $net,
                ])
                    ->setPaper('letter', 'portrait');

                return response()->streamDownload(
                    fn () => print($pdf->output()),
                    'Transactions-' . now()->format('Y-m-d') . '.pdf'
                );
            });
    }
}
