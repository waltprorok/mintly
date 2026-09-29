<x-filament::page>
    <div class="flex max-w-[900px] items-start gap-4 max-md:flex-col">

        {{-- LEFT: Form --}}
        <div class="w-full max-w-[520px] flex-1">
            <form wire:submit.prevent="calculate">
                {{ $this->form }}
            </form>
        </div>

        {{-- RIGHT: Results --}}
        <div class="w-[400px] shrink-0 max-md:w-full">
            <div
                class="
                    w-full rounded-[10px] border border-gray-200
                    bg-white p-5 shadow-sm
                    dark:border-zinc-800 dark:bg-zinc-900 dark:text-gray-200
                    max-md:mt-4
                "
            >
                <div class="mb-3 flex justify-between">
                    <div class="text-gray-500 dark:text-slate-50">
                        Monthly Payment
                    </div>

                    <div class="text-base font-semibold tracking-[0.3px] dark:text-slate-50">
                        ${{ number_format($monthly_payment, 2) }}
                    </div>
                </div>

                @if(($extra_payment ?? 0) > 0)
                    <div class="mb-3 flex justify-between">
                        <div class="text-gray-500 dark:text-slate-50">
                            Extra Payment
                        </div>

                        <div class="text-base font-semibold tracking-[0.3px] dark:text-slate-50">
                            ${{ number_format($extra_payment, 2) }}
                        </div>
                    </div>

                    <div class="mb-3 flex justify-between">
                        <div class="text-gray-500 dark:text-slate-50">
                            Total Monthly Payment
                        </div>

                        <div class="text-base font-semibold tracking-[0.3px] dark:text-slate-50">
                            ${{ number_format($total_monthly_payment, 2) }}
                        </div>
                    </div>
                @endif

                <hr class="my-4 border-0 border-t border-gray-200 dark:border-gray-800">

                <div class="mb-3 flex justify-between">
                    <div class="text-gray-500 dark:text-slate-50">
                        Loan Amount
                    </div>

                    <div class="text-base font-semibold tracking-[0.3px] dark:text-slate-50">
                        ${{ number_format($loan_amount ?? 0, 2) }}
                    </div>
                </div>

                <div class="mb-3 flex justify-between">
                    <div class="text-gray-500 dark:text-slate-50">
                        Total Interest
                    </div>

                    <div class="text-base font-semibold tracking-[0.3px] dark:text-slate-50">
                        ${{ number_format($total_interest, 2) }}
                    </div>
                </div>

                <div class="mb-3 flex justify-between">
                    <div class="text-gray-500 dark:text-slate-50">
                        Total Paid
                    </div>

                    <div class="text-base font-semibold tracking-[0.3px] dark:text-slate-50">
                        ${{ number_format($total_paid, 2) }}
                    </div>
                </div>

                @if($months_saved)
                    <hr class="my-4 border-0 border-t border-gray-200 dark:border-gray-800">

                    <div class="mb-3 flex justify-between">
                        <div class="text-gray-500 dark:text-slate-50">
                            Months Saved
                        </div>

                        <div class="text-base font-semibold tracking-[0.3px] dark:text-slate-50">
                            {{ $months_saved }} months
                        </div>
                    </div>

                    <div class="mb-3 flex justify-between">
                        <div class="text-gray-500 dark:text-slate-50">
                            Years Saved
                        </div>

                        <div class="text-base font-semibold tracking-[0.3px] dark:text-slate-50">
                            {{ number_format($years_saved, 1) }} years
                        </div>
                    </div>

                    <div class="mb-3 flex justify-between">
                        <div class="text-gray-500 dark:text-slate-50">
                            Interest Saved
                        </div>

                        <div class="text-base font-semibold tracking-[0.3px] dark:text-slate-50">
                            ${{ number_format($interest_saved, 2) }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-filament::page>
