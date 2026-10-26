<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\RecurringTransactionService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class RollForwardRecurringTransactions extends Command
{
    protected $signature = 'app:roll-forward-recurring-transactions
                            {userId?}
                            {--date= : Reference month, e.g. 2026-06-01}';

    protected $description = 'Prepare next month recurring transactions for all users';

    public function handle(RecurringTransactionService $service): int
    {
        try {
            $referenceDate = $this->option('date')
                ? Carbon::parse($this->option('date'))->startOfMonth()
                : now()->startOfMonth();
        } catch (Throwable $e) {
            $this->error('Invalid reference date.');

            return self::FAILURE;
        }

        $targetDate = $referenceDate
            ->copy()
            ->addMonthNoOverflow();

        $this->info("Preparing recurring transactions for {$targetDate->format('F Y')}");

        $query = User::query();

        if ($userId = $this->argument('userId')) {
            $query->whereKey($userId);
        }

        $processed = 0;
        $created = 0;
        $failed = 0;

        $query->chunkById(100, function ($users) use (
            $service,
            $referenceDate,
            $targetDate,
            &$processed,
            &$created,
            &$failed
        ) {
            foreach ($users as $user) {
                $processed++;

                try {
                    $count = $service->run(
                        userId: $user->id,
                        nextMonthOnly: true,
                        referenceDate: $referenceDate->copy(),
                    );

                    $created += $count;

                    $this->line(
                        "User {$user->id}: {$count} created"
                    );

                    Log::info('Recurring transactions prepared', [
                        'user_id' => $user->id,
                        'source_period' => $referenceDate->format('Y-m'),
                        'target_period' => $targetDate->format('Y-m'),
                        'created' => $count,
                    ]);

                } catch (Throwable $e) {
                    $failed++;

                    Log::error('Recurring transaction preparation failed', [
                        'user_id' => $user->id,
                        'source_period' => $referenceDate->format('Y-m'),
                        'target_period' => $targetDate->format('Y-m'),
                        'error' => $e->getMessage(),
                    ]);

                    $this->error(
                        "User {$user->id}: Failed - {$e->getMessage()}"
                    );
                }
            }
        });

        $this->newLine();

        $this->info('Recurring transaction preparation complete.');

        $this->table(
            ['Metric', 'Total'],
            [
                ['Users Processed', $processed],
                ['Transactions Created', $created],
                ['Users Failed', $failed],
            ]
        );

        return $failed > 0
            ? self::FAILURE
            : self::SUCCESS;
    }
}
