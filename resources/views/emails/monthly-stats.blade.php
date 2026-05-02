@component('mail::message')

## Hello {{ $user->name ?? 'there' }},

Here is your financial summary for **{{ $month->format('F Y') }}**:

* **Total Income:** ${{ number_format($income, 2) }}
* **Total Expenses:** ${{ number_format($expenses, 2) }}
* **Net:** ${{ number_format($net, 2) }}

### Insights

@if($income > 0)
- You spent **{{ $spendingPercent }}%** of your income
@endif
- Bills paid: **{{ $billsPaidPercent }}%**

Take a moment to review your progress and plan your next month.

@component('mail::button', ['url' => url('/dashboard/budget')])
    View Your Budget
@endcomponent

Stay on top of your money and keep building better habits with {{ config('app.name') }}.

Thanks,<br>
The {{ config('app.name') }} Team

@endcomponent
