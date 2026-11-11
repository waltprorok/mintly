<div class="flex items-center gap-3">

    <label for="budget-month"
        class="text-sm font-medium text-gray-700 dark:text-gray-300"
    >
        Month
    </label>

    <x-filament::input.wrapper class="w-48">
        <x-filament::input.select
            id="budget-month"
            wire:model.live="selectedMonth"
        >
            @foreach ($this->monthOptions() as $value => $label)
                <option value="{{ $value }}">
                    {{ $label }}
                </option>
            @endforeach
        </x-filament::input.select>
    </x-filament::input.wrapper>

</div>
