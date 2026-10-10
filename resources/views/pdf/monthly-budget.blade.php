<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <title>
        Monthly Budget - {{ \Carbon\Carbon::create($year, $month)->format('F Y') }}
    </title>

    <style>
        @page {
            margin: 30px 35px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111827;
            line-height: 1.4;
        }

        /* -------------------------------------------------
         * Report Header
         * ------------------------------------------------- */

        .report-title {
            font-size: 20px;
            font-weight: bold;
            margin: 0 0 3px;
        }

        .report-period {
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 24px;
        }

        /* -------------------------------------------------
         * Tables
         * ------------------------------------------------- */

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 7px 8px;
            vertical-align: middle;
        }

        .amount {
            width: 140px;
            text-align: right;
            white-space: nowrap;
        }

        .date {
            width: 90px;
            color: #6b7280;
            white-space: nowrap;
        }

        .payment {
            width: 120px;
            color: #6b7280;
        }

        /* -------------------------------------------------
         * Main Sections
         * ------------------------------------------------- */

        .section-title {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 8px 6px;
            border-bottom: 1px solid #d1d5db;
            margin-top: 18px;
        }

        .column-header th {
            font-size: 9px;
            color: #6b7280;
            text-transform: uppercase;
            border-bottom: 1px solid #e5e7eb;
        }

        .column-header th:first-child {
            text-align: left;
        }

        .column-header .amount {
            text-align: right;
        }

        /* -------------------------------------------------
         * Transactions
         * ------------------------------------------------- */

        .transaction-row td {
            border-bottom: 1px solid #f3f4f6;
        }

        .transaction-name {
            padding-left: 22px;
        }

        /* -------------------------------------------------
         * Categories
         * ------------------------------------------------- */

        .category-title td {
            font-weight: bold;
            padding-top: 14px;
            padding-bottom: 5px;
        }

        .category-total td {
            font-weight: bold;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
        }

        .category-total-label {
            padding-left: 22px;
        }

        /* -------------------------------------------------
         * Totals
         * ------------------------------------------------- */

        .section-total td {
            font-size: 12px;
            font-weight: bold;
            background: #f3f4f6;
            border-top: 1px solid #d1d5db;
            border-bottom: 1px solid #d1d5db;
            padding-top: 9px;
            padding-bottom: 9px;
        }

        /* -------------------------------------------------
         * Monthly Summary
         * ------------------------------------------------- */

        .summary {
            margin-top: 28px;
        }

        .summary-title {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 8px;
            border-bottom: 1px solid #d1d5db;
        }

        .summary td {
            padding: 8px;
        }

        .summary-row td {
            border-bottom: 1px solid #e5e7eb;
        }

        .net-total td {
            font-size: 13px;
            font-weight: bold;
            background: #f3f4f6;
            border-top: 2px solid #9ca3af;
            padding-top: 10px;
            padding-bottom: 10px;
        }

        /* Prevent category sections from splitting awkwardly. */
        .category-block {
            page-break-inside: avoid;
        }

        .footer {
            margin-top: 25px;
            padding-top: 8px;
            border-top: 1px solid #e5e7eb;
            font-size: 9px;
            color: #9ca3af;
            text-align: center;
        }
    </style>
</head>

<body>

{{-- =========================================================
     HEADER
     ========================================================= --}}

<div class="report-title">
    Monthly Budget
</div>

<div class="report-period">
    {{ \Carbon\Carbon::create($year, $month)->format('F Y') }}
</div>


{{-- =========================================================
     INCOME
     ========================================================= --}}

<div class="section-title">
    Income
</div>

<table>
    <thead>
    <tr class="column-header">
        <th>Description</th>
        <th class="date">Date</th>
        <th class="payment">Payment</th>
        <th class="amount">Amount</th>
    </tr>
    </thead>

    <tbody>

    @forelse ($income as $transaction)

        <tr class="transaction-row">
            <td class="transaction-name">
                {{ $transaction->merchant ?: ($transaction->category?->name ?? 'Income') }}
            </td>

            <td class="date">
                {{ $transaction->due_at?->format('M j') }}
            </td>

            <td class="payment">
                @if ($transaction->payment_method)
                    {{ str($transaction->payment_method)
                        ->replace('_', ' ')
                        ->title() }}
                @endif
            </td>

            <td class="amount">
                ${{ number_format($transaction->amount, 2) }}
            </td>
        </tr>

    @empty

        <tr>
            <td colspan="4">
                No income transactions for this period.
            </td>
        </tr>

    @endforelse

    <tr class="section-total">
        <td colspan="3">
            Total Income
        </td>

        <td class="amount">
            ${{ number_format($incomeTotal, 2) }}
        </td>
    </tr>

    </tbody>
</table>


{{-- =========================================================
     EXPENSES
     ========================================================= --}}

<div class="section-title">
    Expenses
</div>

<table>
    <thead>
    <tr class="column-header">
        <th>Description</th>
        <th class="date">Date</th>
        <th class="payment">Payment</th>
        <th class="amount">Amount</th>
    </tr>
    </thead>

    <tbody>

    @forelse ($expensesByCategory as $category => $transactions)

        {{-- Category --}}
        <tr class="category-title">
            <td colspan="4">
                {{ $category }}
            </td>
        </tr>

        {{-- Transactions --}}
        @foreach ($transactions as $transaction)

            <tr class="transaction-row">
                <td class="transaction-name">
                    {{ $transaction->merchant ?: $category }}
                </td>

                <td class="date">
                    {{ $transaction->due_at?->format('M j') }}
                </td>

                <td class="payment">
                    @if ($transaction->payment_method)
                        {{ str($transaction->payment_method)
                            ->replace('_', ' ')
                            ->title() }}
                    @endif
                </td>

                <td class="amount">
                    ${{ number_format($transaction->amount, 2) }}
                </td>
            </tr>

        @endforeach

        {{-- Category Total --}}
        <tr class="category-total">
            <td class="category-total-label" colspan="3">
                Total {{ $category }}
            </td>

            <td class="amount">
                ${{ number_format($transactions->sum('amount'), 2) }}
            </td>
        </tr>

    @empty

        <tr>
            <td colspan="4">
                No expense transactions for this period.
            </td>
        </tr>

    @endforelse


    {{-- Total Expenses --}}

    <tr class="section-total">
        <td colspan="3">
            Total Expenses
        </td>

        <td class="amount">
            ${{ number_format($expenseTotal, 2) }}
        </td>
    </tr>

    </tbody>
</table>


{{-- =========================================================
     MONTHLY SUMMARY
     ========================================================= --}}

<table class="summary">

    <tr>
        <td colspan="2" class="summary-title">
            Monthly Summary
        </td>
    </tr>

    <tr class="summary-row">
        <td>
            Total Income
        </td>

        <td class="amount">
            ${{ number_format($incomeTotal, 2) }}
        </td>
    </tr>

    <tr class="summary-row">
        <td>
            Total Expenses
        </td>

        <td class="amount">
            ${{ number_format($expenseTotal, 2) }}
        </td>
    </tr>

    <tr class="net-total">
        <td>
            NET BUDGET
        </td>

        <td class="amount">
            @if ($net < 0)
                (${{ number_format(abs($net), 2) }})
            @else
                ${{ number_format($net, 2) }}
            @endif
        </td>
    </tr>

</table>


{{-- =========================================================
     FOOTER
     ========================================================= --}}

<div class="footer">
    Generated {{ now()->format('M j, Y') }}
</div>

</body>
</html>
