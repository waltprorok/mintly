{{-- resources/views/filament/pages/loan-calculator.blade.php --}}
<x-filament::page>

    <form wire:submit.prevent="calculate" class="space-y-4">
        {{ $this->form }}

    </form>

    <div class="mt-6 space-y-2">
        <div><strong>Monthly Payment:</strong> ${{ number_format($monthly_payment, 2) }}</div>
        <br>
        <div><strong>Total Interest:</strong> ${{ number_format($total_interest, 2) }}</div>
        <br>
        <div><strong>Total Paid:</strong> ${{ number_format($total_paid, 2) }}</div>
    </div>

</x-filament::page>
