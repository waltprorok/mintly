<?php

namespace App\Services;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReportPeriod
{
    public static function latest(): string
    {
        $newest = Transaction::query()
            ->where('user_id', auth()->id())
            ->max('due_at');

        return $newest
            ? Carbon::parse($newest)->format('Y-m')
            : now()->format('Y-m');
    }

    public static function range(?string $filter): array
    {
        $filter ??= self::latest();

        if (str_starts_with($filter, 'year-')) {
            $year = (int) substr($filter, 5);

            $start = Carbon::create($year, 1, 1)->startOfYear();

            return [
                $start,
                $start->copy()->endOfYear(),
            ];
        }

        [$year, $month] = explode('-', $filter);

        $start = Carbon::create(
            (int) $year,
            (int) $month,
            1
        )->startOfMonth();

        return [
            $start,
            $start->copy()->endOfMonth(),
        ];
    }

    public static function filters(): array
    {
        $dates = Transaction::query()
            ->where('user_id', Auth::id())
            ->whereNotNull('due_at')
            ->orderByDesc('due_at')
            ->pluck('due_at');

        $months = $dates
            ->map(fn ($date) => Carbon::parse($date)->format('Y-m'))
            ->unique();

        $filters = [];

        foreach ($months as $month) {
            $year = substr($month, 0, 4);

            if (! isset($filters["year-{$year}"])) {
                $filters["year-{$year}"] = "{$year} — Full Year";
            }

            $filters[$month] = Carbon::createFromFormat('!Y-m', $month)
                ->format('F Y');
        }

        return $filters;
    }
}
