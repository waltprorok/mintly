<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RollForwardRecurringTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:roll-forward-recurring-transactions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Keep data up to date for each month.';

    /**
     * Execute the console command.
     */

    public function handle(): void
    {
        $users = User::all();

        $this->info("Starting roll forward for {$users->count()} users");

        foreach ($users as $user) {

            try {
                $lastTransaction = Transaction::where('user_id', $user->id)
                    ->latest('due_at')
                    ->first();

                if (! $lastTransaction) {
                    continue;
                }

                $lastMonth = Carbon::parse($lastTransaction->due_at)->startOfMonth();
                $currentMonth = now()->startOfMonth();

                while ($lastMonth->lte($currentMonth))
                {

                    $this->rollForward($user, $lastMonth);

                    $lastMonth->addMonth();
                }

            } catch (\Throwable $e) {

                Log::error('Roll forward failed for user', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);

                $this->error("Failed for user {$user->id}");

                continue;
            }
        }

        $this->info('Roll forward completed');
    }

    protected function rollForward($user, $month): void
    {
        $nextMonth = $month->copy()->addMonth();
        $lastDay = $nextMonth->copy()->endOfMonth()->day;

        $transactions = Transaction::query()
            ->where('user_id', $user->id)
            ->where('is_recurring', true)
            ->whereMonth('due_at', $month->month)
            ->whereYear('due_at', $month->year)
            ->get();

        $created = 0;

        foreach ($transactions as $transaction) {

            try {
                $day = $transaction->due_at->day;
                $newDay = min($day, $lastDay);

                $newDate = Carbon::create(
                    $nextMonth->year,
                    $nextMonth->month,
                    $newDay
                );

                Transaction::updateOrCreate(
                    [
                        'user_id' => $transaction->user_id,
                        'category_id' => $transaction->category_id,
                        'merchant' => $transaction->merchant,
                        'due_at' => $newDate,
                    ],
                    [
                        'amount' => $transaction->amount,
                        'type' => $transaction->type,
                        'payment_method' => $transaction->payment_method,
                        'notes' => null,
                        'is_recurring' => $transaction->is_recurring,
                        'status' => false,
                    ]
                );

                $created++;

            } catch (\Throwable $e) {

                Log::error('Transaction roll forward failed', [
                    'user_id' => $user->id,
                    'transaction_id' => $transaction->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Roll forward month processed', [
            'user_id' => $user->id,
            'month' => $month->format('Y-m'),
            'created' => $created,
        ]);
    }
}
