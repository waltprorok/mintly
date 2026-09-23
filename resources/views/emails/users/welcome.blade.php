@component('mail::message')

# Welcome to {{ config('app.name') }}, {{ $user->name }}!

Thanks for signing up for **{{ config('app.name') }}** — your new personal budgeting companion.

We're excited to help you take control of your finances and build better money habits.

With Mintly you can:

* Organize your income and expenses into categories
* Plan your monthly budget and see where your money is going
* Automatically carry recurring transactions into future months
* See your income and expenses broken down week by week
* Track which bills have been paid and what is still coming up
* Understand your financial habits
* Work toward your financial goals

## Next steps

* [Create your categories]({{ url('/dashboard/categories') }}) to get started
* [Add your transactions]({{ url('/dashboard/transactions') }}) in seconds
* Instantly see insights into your spending habits

Click below to get started with your dashboard.

@component('mail::button', ['url' => url('/dashboard')])
    Go To My Dashboard
@endcomponent

Questions or feedback? We're here to help.

[Contact the Mintly Team](mailto:{{ config('support.email') }}).

Welcome aboard!

Thanks,

The {{ config('app.name') }} Team

@endcomponent
