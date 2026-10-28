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

                // Must be the same transaction type.
                if ($transaction->type !== $candidate->type) {
                    continue;
                }

                // Must be within the allowed date range.
                if (
                    Carbon::parse($transaction->due_at)
                        ->diffInDays(Carbon::parse($candidate->due_at)) > $daysTolerance
                ) {
                    break;
                }

                // Must be the same normalized merchant.
                if (
                    MerchantNormalizer::normalize($transaction->merchant)
                    !== MerchantNormalizer::normalize($candidate->merchant)
                ) {
                    continue;
                }

                $difference = abs(
                    (float) $transaction->amount - (float) $candidate->amount
                );

                $maxAmount = max(
                    (float) $transaction->amount,
                    (float) $candidate->amount
                );

                $percentageDifference = $maxAmount > 0
                    ? ($difference / $maxAmount) * 100
                    : 0;

                // Ignore amounts differing by more than 10%.
                if ($percentageDifference > 10) {
                    continue;
                }

                $duplicates->push([
                    'original' => $transaction,
                    'duplicate' => $candidate,
                    'confidence' => $difference < 0.01
                        ? 'High'
                        : 'Potential',
                ]);
            }
        }

        return $duplicates;
    }
}
