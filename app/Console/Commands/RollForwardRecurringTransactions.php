<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\RecurringTransactionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class RollForwardRecurringTransactions extends Command
{
    protected $signature = 'app:roll-forward-recurring-transactions';

    protected $description = 'Prepare next month recurring transactions for all users';

    public function handle(): void
    {
        $service = app(RecurringTransactionService::class);

        $users = User::all();

        $referenceDate = now()->startOfMonth();

        $targetPeriod = $referenceDate
            ->copy()
            ->addMonthNoOverflow()
            ->format('F Y');

        $this->info("Processing {$users->count()} users for {$targetPeriod}");

        foreach ($users as $user) {
            try {
                $created = $service->run(
                    userId: $user->id,
                    nextMonthOnly: true,
                    referenceDate: $referenceDate,
                );

                $this->line(
                    "User {$user->id}: {$created} created"
                );

                Log::info('Recurring transactions prepared', [
                    'user_id' => $user->id,
                    'source_period' => $referenceDate->format('Y-m'),
                    'target_period' => $referenceDate
                        ->copy()
                        ->addMonthNoOverflow()
                        ->format('Y-m'),
                    'created' => $created,
                ]);
            } catch (Throwable $e) {
                Log::error(
                    'Recurring transaction preparation failed',
                    [
                        'user_id' => $user->id,
                        'source_period' => $referenceDate->format('Y-m'),
                        'target_period' => $referenceDate
                            ->copy()
                            ->addMonthNoOverflow()
                            ->format('Y-m'),
                        'error' => $e->getMessage(),
                    ]
                );

                $this->error("User {$user->id} failed");
            }
        }

        $this->info('Done');
    }
}
