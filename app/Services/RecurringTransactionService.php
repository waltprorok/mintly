<?php

namespace App\Services;

use App\Models\Transaction;

class RecurringTransactionService
{
    public function run(int $userId, bool $nextMonthOnly = false): int
    {
        $now = now();
        $nextMonth = $now->copy()->addMonth();

        $targetEnd = $nextMonthOnly
            ? $nextMonth->copy()->endOfMonth()
            : $now;

        $transactions = Transaction::query()
            ->where('user_id', $userId)
            ->whereNotNull('recurring_rule')
            ->where('recurring_rule', '!=', 'once')
            ->orderBy('due_at')
            ->get();

        $created = 0;

        foreach ($transactions as $transaction) {

            if ($transaction->due_at->gt($now)) {
                continue;
            }

            $currentDate = $transaction->due_at->copy();
            $day = $transaction->due_at->day;

            $iterations = 0;

            while (true) {

                if ($iterations++ > 50) {
                    break;
                }

                $nextDate = match ($transaction->recurring_rule) {

                    'weekly' => $currentDate->copy()->addWeek(),

                    'biweekly' => $currentDate->copy()->addWeeks(2),

                    'monthly' => tap(
                        $currentDate->copy()->addMonthNoOverflow(),
                        fn($d) => $d->day(min($day, $d->copy()->endOfMonth()->day))
                    ),

                    'quarterly' => tap(
                        $currentDate->copy()->addMonthsNoOverflow(3),
                        fn($d) => $d->day(min($day, $d->copy()->endOfMonth()->day))
                    ),

                    'yearly' => tap(
                        $currentDate->copy()->addYearNoOverflow(),
                        fn($d) => $d->day(min($day, $d->copy()->endOfMonth()->day))
                    ),

                    default => null,
                };

                if (! $nextDate || $nextDate->gt($targetEnd)) {
                    break;
                }

                if ($nextMonthOnly && $nextDate->format('Y-m') !== $nextMonth->format('Y-m')) {
                    $currentDate = $nextDate;
                    continue;
                }

                $model = Transaction::updateOrCreate(
                    [
                        'user_id' => $transaction->user_id,
                        'merchant' => $transaction->merchant,
                        'type' => $transaction->type,
                        'recurring_rule' => $transaction->recurring_rule,
                        'due_at' => $nextDate,
                    ],
                    [
                        'category_id' => $transaction->category_id,
                        'amount' => $transaction->amount,
                        'payment_method' => $transaction->payment_method,
                        'notes' => null,
                    ]
                );

                if ($model->wasRecentlyCreated) {
                    $created++;
                }

                $currentDate = $nextDate;
            }
        }

        return $created;
    }
}
