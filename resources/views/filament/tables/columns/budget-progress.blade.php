@php
    $record = $getRecord();

    $planned = (float) $record->amount;
    $actual = (float) ($record->paid_spending ?? 0);

    $percentage = $planned > 0
        ? ($actual / $planned) * 100
        : ($actual > 0 ? 100 : 0);

    $width = min($percentage, 100);

    $color = match (true) {
        $percentage > 100 => 'bg-red-500',
        $percentage >= 80 => 'bg-yellow-500',
        default => 'bg-green-500',
    };
@endphp

<div class="min-w-36 space-y-1">
    <div class="flex justify-between text-xs">
        <span>{{ number_format($percentage, 0) }}%</span>
        <span>
            ${{ number_format($actual, 2) }}
            /
            ${{ number_format($planned, 2) }}
        </span>
    </div>

    <div class="h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
        <div
            class="h-full rounded-full {{ $color }}"
            style="width: {{ $width }}%"
        ></div>
    </div>
</div>
