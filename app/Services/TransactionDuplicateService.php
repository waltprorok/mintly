<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TransactionDuplicateService
{
    public function find(Builder $query, int $daysTolerance = 1): Collection
    {
        $transactions = (clone $query)->orderBy('due_at')->get();

        $duplicates = collect();

        foreach ($transactions as $index => $transaction) {
            foreach ($transactions->slice($index + 1) as $candidate) {
                if ($transaction->type !== $candidate->type) {
                    continue;
                }

                if ((float) $transaction->amount !== (float) $candidate->amount) {
                    continue;
                }

                if (
                    Carbon::parse($transaction->due_at)
                        ->diffInDays(Carbon::parse($candidate->due_at)) > $daysTolerance
                ) {
                    break;
                }

                if (MerchantNormalizer::normalize($transaction->merchant)
                    !== MerchantNormalizer::normalize($candidate->merchant))
                {
                    continue;
                }

                $duplicates->push([
                    'original' => $transaction,
                    'duplicate' => $candidate,
                    'confidence' => 'High',
                ]);
            }
        }

        return $duplicates;
    }
}
