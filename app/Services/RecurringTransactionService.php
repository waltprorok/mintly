<?php

namespace App\Services;

use App\Models\Transaction;
use Carbon\Carbon;

class RecurringTransactionService
{
    public function run(int $userId, bool $nextMonthOnly = false, ?Carbon $referenceDate = null): int
    {
        $referenceDate ??= now();

        $sourceStart = $referenceDate->copy()->startOfMonth();
        $sourceEnd = $referenceDate->copy()->endOfMonth();

        $targetStart = $nextMonthOnly
            ? $referenceDate->copy()->addMonthNoOverflow()->startOfMonth()
            : $sourceStart->copy();

        $targetEnd = $targetStart->copy()->endOfMonth();

        $quarterlyStart = $targetStart->copy()->subMonthsNoOverflow(3)->startOfMonth();
        $quarterlyEnd = $quarterlyStart->copy()->endOfMonth();

        $semiannualStart = $targetStart->copy()->subMonthsNoOverflow(6)->startOfMonth();
        $semiannualEnd = $semiannualStart->copy()->endOfMonth();

        $yearlyStart = $targetStart->copy()->subYearNoOverflow()->startOfMonth();
        $yearlyEnd = $yearlyStart->copy()->endOfMonth();

        $transactions = Transaction::query()
            ->where('user_id', $userId)
            ->whereNotNull('recurring_rule')
            ->where('recurring_rule', '!=', 'once')
            ->where(function ($query) use (
                $sourceStart,
                $sourceEnd,
                $quarterlyStart,
                $quarterlyEnd,
                $semiannualStart,
                $semiannualEnd,
                $yearlyStart,
                $yearlyEnd
            ) {
                $query
                    ->where(function ($query) use ($sourceStart, $sourceEnd) {
                        $query
                            ->whereIn('recurring_rule', [
                                'weekly',
                                'biweekly',
                                'monthly',
                            ])
                            ->whereBetween('due_at', [$sourceStart, $sourceEnd]);
                    })
                    ->orWhere(function ($query) use ($quarterlyStart, $quarterlyEnd) {
                        $query
                            ->where('recurring_rule', 'quarterly')
                            ->whereBetween('due_at', [$quarterlyStart, $quarterlyEnd]);
                    })
                    ->orWhere(function ($query) use ($semiannualStart, $semiannualEnd) {
                        $query
                            ->where('recurring_rule', 'semiannually')
                            ->whereBetween('due_at', [$semiannualStart, $semiannualEnd]);
                    })
                    ->orWhere(function ($query) use ($yearlyStart, $yearlyEnd) {
                        $query
                            ->where('recurring_rule', 'yearly')
                            ->whereBetween('due_at', [$yearlyStart, $yearlyEnd]);
                    });
            })
            ->orderBy('due_at')
            ->get();

        $created = 0;

        foreach ($transactions as $transaction) {
            $currentDate = $transaction->due_at->copy();
            $day = $transaction->due_at->day;
            $iterations = 0;

            while ($iterations++ <= 50) {
                $nextDate = match ($transaction->recurring_rule) {
                    'weekly' => $currentDate->copy()->addWeek(),

                    'biweekly' => $currentDate->copy()->addWeeks(2),

                    'monthly' => tap(
                        $currentDate->copy()->addMonthNoOverflow(),
                        fn(Carbon $date) => $date->day(
                            min($day, $date->copy()->endOfMonth()->day)
                        )
                    ),

                    'quarterly' => tap(
                        $currentDate->copy()->addMonthsNoOverflow(3),
                        fn(Carbon $date) => $date->day(
                            min($day, $date->copy()->endOfMonth()->day)
                        )
                    ),

                    'semiannually' => tap(
                        $currentDate->copy()->addMonthsNoOverflow(6),
                        fn(Carbon $date) => $date->day(
                            min($day, $date->copy()->endOfMonth()->day)
                        )
                    ),

                    'yearly' => tap(
                        $currentDate->copy()->addYearNoOverflow(),
                        fn(Carbon $date) => $date->day(
                            min($day, $date->copy()->endOfMonth()->day)
                        )
                    ),

                    default => null,
                };

                if (! $nextDate || $nextDate->gt($targetEnd)) {
                    break;
                }

                if ($nextDate->lt($targetStart)) {
                    $currentDate = $nextDate;

                    continue;
                }

                $week = (int)ceil($nextDate->day / 7);

                $query = Transaction::query()
                    ->where('user_id', $transaction->user_id)
                    ->where('merchant', $transaction->merchant)
                    ->where('type', $transaction->type)
                    ->whereYear('due_at', $nextDate->year)
                    ->whereMonth('due_at', $nextDate->month);

                if (config('database.default') === 'sqlite') {
                    $query->whereRaw(
                        '((CAST(strftime("%d", due_at) AS INTEGER) - 1) / 7) + 1 = ?',
                        [$week]
                    );
                } else {
                    $query->whereRaw(
                        'CEIL(DAY(due_at) / 7) = ?',
                        [$week]
                    );
                }

                $existing = $query->first();

                if ($existing) {
                    $existing->update([
                        'category_id' => $transaction->category_id,
                        'amount' => $transaction->amount,
                        'payment_method' => $transaction->payment_method,
                    ]);
                } else {
                    Transaction::create([
                        'user_id' => $transaction->user_id,
                        'merchant' => $transaction->merchant,
                        'type' => $transaction->type,
                        'due_at' => $nextDate,
                        'category_id' => $transaction->category_id,
                        'amount' => $transaction->amount,
                        'payment_method' => $transaction->payment_method,
                        'recurring_rule' => $transaction->recurring_rule,
                        'notes' => null,
                    ]);

                    $created++;
                }

                $currentDate = $nextDate;
            }
        }

        return $created;
    }
}
