<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TransactionDuplicateService
{
    public function find(Builder $query, int $daysTolerance = 3): Collection
    {
        $transactions = (clone $query)
            ->orderBy('due_at')
            ->get();

        $duplicates = collect();

        foreach ($transactions as $index => $transaction) {
            foreach ($transactions->slice($index + 1) as $candidate) {

                if ($transaction->type !== $candidate->type) {
                    continue;
                }

                if (
                    MerchantNormalizer::normalize($transaction->merchant)
                    !== MerchantNormalizer::normalize($candidate->merchant)
                ) {
                    continue;
                }

                $daysDifference = Carbon::parse($transaction->due_at)
                    ->diffInDays(Carbon::parse($candidate->due_at));

                $sameMonth = Carbon::parse($transaction->due_at)
                    ->isSameMonth(Carbon::parse($candidate->due_at));

                $difference = abs(
                    (float)$transaction->amount - (float)$candidate->amount
                );

                $sameAmount = $difference < 0.01;

                $monthlyAndOnce = (
                        $transaction->recurring_rule === 'monthly' &&
                        $candidate->recurring_rule === 'once'
                    ) || (
                        $transaction->recurring_rule === 'once' &&
                        $candidate->recurring_rule === 'monthly'
                    );

                // Monthly planned transaction vs. imported one-time transaction.
                if ($monthlyAndOnce && $sameMonth && $daysDifference <= $daysTolerance) {

                    if ($difference <= 1.00) {
                        $duplicates->push([
                            'original' => $transaction,
                            'duplicate' => $candidate,
                            'confidence' => 'Potential',
                        ]);
                    }

                    continue;
                }

                // Exact matches only for all other transactions.
                if ($daysDifference === 0 && $sameAmount) {
                    $duplicates->push([
                        'original' => $transaction,
                        'duplicate' => $candidate,
                        'confidence' => 'High',
                    ]);
                }
            }
        }

        return $duplicates;
    }
}
