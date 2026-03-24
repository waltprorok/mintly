@component('mail::message')

## Hello {{ $user->name ?? 'there' }},

Here is your financial summary for **{{ $month->format('F Y') }}**:

* **Total Income:** ${{ number_format($income, 2) }}
* **Total Expenses:** ${{ number_format($expenses, 2) }}
* **Net:** ${{ number_format($net, 2) }}

Keep tracking your finances with Mintly!

Thanks,<br>
{{ config('app.name') }}

@endcomponent
