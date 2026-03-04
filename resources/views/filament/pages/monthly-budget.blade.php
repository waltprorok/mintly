<x-filament-panels::page>

    <div class="mb-6 text-lg">
        Monthly Income:
        <span class="font-semibold text-success-600">
            ${{ number_format($income, 2) }}
        </span>

{{--        <div class="font-medium">--}}
{{--            Total Expenses:--}}
{{--            ${{ number_format($this->table->getRecords()->sum('amount'),2) }}--}}
{{--        </div>--}}

{{--        <div class="text-lg font-bold">--}}
{{--            Surplus:--}}
{{--            <span class="{{ $income - $this->table->getRecords()->sum('amount') >= 0 ? 'text-success-600' : 'text-danger-600' }}">--}}
{{--            ${{ number_format($income - $this->table->getRecords()->sum('amount'),2) }}--}}
{{--        </span>--}}
        </div>

    {{ $this->table }}


</x-filament-panels::page>
