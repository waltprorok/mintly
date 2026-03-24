<?php

namespace App\Console\Commands;

use App\Mail\MonthlyStatsMail;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendMonthlyStatsEmails extends Command
{
    protected $signature = 'app:send-monthly-stats';
    protected $description = 'Send monthly financial stats to all users';

    public function handle(): bool
    {
        $startedAt = now();
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();

        $this->info("Sending monthly stats from {$start} to {$end}");

        Log::info('Monthly stats job started', [
            'started_at' => $startedAt,
            'range' => [$start, $end],
        ]);

        User::chunk(50, function ($users) use ($start, $end) {
            foreach ($users as $user) {

                $transactions = Transaction::where('user_id', $user->id)
                    ->whereBetween('due_at', [$start, $end])
                    ->get();

                $income = $transactions->where('type', 'income')->sum('amount');
                $expenses = $transactions->where('type', 'expense')->sum('amount');

                $net = $income - $expenses;

                Mail::to($user->email)->queue(
                    new MonthlyStatsMail($user, $income, $expenses, $net, $start)
                );
            }
        });


        $finishedAt = now();
        $duration = $finishedAt->diffInSeconds($startedAt);

        $this->info('Monthly stats emails sent successfully.');

        Log::info('Monthly stats job finished', [
            'finished_at' => $finishedAt,
            'duration_seconds' => $duration,
        ]);

        return true;
    }
}
