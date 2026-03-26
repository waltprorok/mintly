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
    protected $description = 'Backfill recurring transactions for all users';

    public function handle(): void
    {
        $service = app(RecurringTransactionService::class);

        $users = User::all();

        $this->info("Processing {$users->count()} users");

        foreach ($users as $user) {
            try {

                $created = $service->run($user->id, false);

                $this->line("User {$user->id}: {$created} created");

                Log::info('User processed', [
                    'user_id' => $user->id,
                    'created' => $created,
                ]);

            } catch (Throwable $e) {

                Log::error('Recurring transaction failed for user', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);

                $this->error("User {$user->id} failed");

                continue;
            }
        }

        $this->info('Done');
    }
}
