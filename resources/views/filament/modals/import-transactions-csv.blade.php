<div class="space-y-5">
    {{-- Recommended Import --}}
    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">

        <div class="flex items-start gap-3">

            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary-50 dark:bg-primary-500/10">
                <x-heroicon-o-document-text class="h-5 w-5 text-primary-600 dark:text-primary-400" />
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-950 dark:text-white">
                    Recommended: Import a Full Month
                </h3>

                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Import a full month of transactions to establish an accurate
                    budget. Mintly will organize your transactions so you can
                    review categories and recurring frequencies.
                </p>
            </div>

        </div>

    </div>

    {{-- CSV Format --}}
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-gray-950 dark:text-white">
                    Simple CSV Format
                </h3>

                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Your file can use the following column structure.
                </p>
            </div>

            <span class="rounded-full bg-success-50 px-2.5 py-1 text-xs font-medium text-success-700 dark:bg-success-500/10 dark:text-success-400">
                Example
            </span>

        </div>

        {{-- CSV Preview --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">

                    <thead class="bg-gray-100 text-xs uppercase tracking-wide text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Date</th>
                        <th class="px-4 py-3 font-semibold">Description</th>
                        <th class="px-4 py-3 font-semibold">Category</th>
                        <th class="px-4 py-3 text-right font-semibold">Amount</th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-700 dark:bg-gray-900">

                    <tr>
                        <td class="whitespace-nowrap px-4 py-3 text-gray-600 dark:text-gray-300">
                            09/03/2026
                        </td>

                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                            Paycheck
                        </td>

                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                            Paycheck
                        </td>

                        <td class="whitespace-nowrap px-4 py-3 text-right font-medium text-green-600 dark:text-green-400">
                            +2,800.00
                        </td>
                    </tr>

                    <tr>
                        <td class="whitespace-nowrap px-4 py-3 text-gray-600 dark:text-gray-300">
                            09/04/2026
                        </td>

                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                            Mortgage
                        </td>

                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                            House
                        </td>

                        <td class="whitespace-nowrap px-4 py-3 text-right font-medium text-red-600 dark:text-red-400">
                            -1,609.66
                        </td>
                    </tr>

                    <tr>
                        <td class="whitespace-nowrap px-4 py-3 text-gray-600 dark:text-gray-300">
                            09/06/2026
                        </td>

                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                            Groceries
                        </td>

                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                            Groceries
                        </td>

                        <td class="whitespace-nowrap px-4 py-3 text-right font-medium text-red-600 dark:text-red-400">
                            -160.94
                        </td>
                    </tr>

                    </tbody>
                </table>
            </div>
        </div>

        {{-- Format Explanation --}}
        <div class="flex flex-wrap gap-2">
            <span class="rounded-md bg-gray-100 px-2.5 py-1.5 text-xs text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                Date: MM/DD/YYYY
            </span>

            <span class="rounded-md bg-gray-100 px-2.5 py-1.5 text-xs text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                Negative = Expense
            </span>

            <span class="rounded-md bg-gray-100 px-2.5 py-1.5 text-xs text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                Positive = Income
            </span>

            <span class="rounded-md bg-gray-100 px-2.5 py-1.5 text-xs text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                Category: Optional
            </span>

        </div>

    </div>

    {{-- Helpful Information --}}
    <div class="rounded-lg border border-blue-200 bg-blue-50 p-3 dark:border-blue-900 dark:bg-blue-950/30">
        <div class="flex items-start gap-2">
            <x-heroicon-o-information-circle class="mt-0.5 h-5 w-5 shrink-0 text-blue-600 dark:text-blue-400" />

            <p class="text-sm text-blue-800 dark:text-blue-300">
                <strong>Already have a bank CSV?</strong>
                You can upload it directly. Mintly attempts to identify
                transaction dates, descriptions, and amounts automatically.
                Credit card payments and transfers are excluded when recognized.
            </p>
        </div>
    </div>
</div>
