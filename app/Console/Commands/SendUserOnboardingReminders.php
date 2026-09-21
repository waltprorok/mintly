<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserEmailReminder;
use App\Notifications\NoCategoriesCreatedNotification;
use App\Notifications\NoTransactionsCreatedNotification;
use Illuminate\Console\Command;
use Log;

class SendUserOnboardingReminders extends Command
{
    protected $signature = 'users:send-onboarding-reminders';

    protected $description = 'Send onboarding reminders to users who have not created categories or transactions';

    public function handle(): int
    {
        $this->sendCategoryReminders();
        $this->sendTransactionReminders();

        return self::SUCCESS;
    }

    private function sendCategoryReminders(): void
    {
        $users = User::whereDoesntHave('categories')
            ->whereDoesntHave('emailReminders', function ($query) {
                $query->where('type', 'no_categories');
            })
            ->where('created_at', '<=', now()->subDays(3))
            ->get();

        foreach ($users as $user) {
            $user->notify(new NoCategoriesCreatedNotification());

            UserEmailReminder::create([
                'user_id' => $user->id,
                'type' => 'no_categories',
                'sent_at' => now(),
            ]);

            Log::info('Category reminder sent', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
            $this->info("Category reminder sent to {$user->email}");
        }
    }

    private function sendTransactionReminders(): void
    {
        $users = User::whereHas('categories')
            ->whereDoesntHave('transactions')
            ->whereDoesntHave('emailReminders', function ($query) {
                $query->where('type', 'no_transactions');
            })
            ->where('created_at', '<=', now()->subDays(5))
            ->get();

        foreach ($users as $user) {
            $user->notify(new NoTransactionsCreatedNotification());

            UserEmailReminder::create([
                'user_id' => $user->id,
                'type' => 'no_transactions',
                'sent_at' => now(),
            ]);

            Log::info('Transaction reminder sent', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
            $this->info("Transaction reminder sent to {$user->email}");
        }
    }
}
