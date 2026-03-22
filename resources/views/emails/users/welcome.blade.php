@component('mail::message')

# Welcome to {{ config('app.name') }}, {{ $user->name }}!

Thanks for signing up for **{{ config('app.name') }}** — your new personal budgeting companion.

We're excited to help you take control of your finances and build better money habits.

With Mintly you can:

* Track your spending
* Set and monitor budgets
* Understand your financial habits
* Work toward your financial goals

Click below to get started with your dashboard.

@component('mail::button', ['url' => url('/dashboard')])
    Go To My Dashboard
@endcomponent

Next steps:

* Create your default categories to get started
* Add your transactions in seconds
* Instantly see insights into your spending habits

If you ever need help or have feedback, we’d love to hear from you.

Welcome aboard!

Thanks,

The {{ config('app.name') }} Team

@endcomponent
