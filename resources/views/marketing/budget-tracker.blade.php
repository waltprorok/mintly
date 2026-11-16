@extends('layouts.marketing-feature')
@section('title', 'Budget Tracker | Track Monthly Spending by Category')
@section('description', 'Track your monthly budget with Mintly Budget Tracker. Set category budgets, compare planned versus actual expenses, and see how much money remains.')
@section('canonical', 'https://mintlybudget.com/budget-tracker')
@section('content')

    {{-- Hero --}}
    <section class="py-5 bg-white border-bottom">
        <div class="container">

            <div class="row align-items-center g-5">

                <div class="col-lg-6">

                <span class="badge bg-success mb-3">
                    Monthly Budget Tracking
                </span>

                    <h1 class="display-5 fw-bold mb-4">
                        Track Your Budget.
                        <span class="text-success">Control Your Spending.</span>
                    </h1>

                    <p class="lead text-secondary">
                        Take the guesswork out of budgeting with Mintly Budget Tracker.
                        Set monthly budgets for individual spending categories,
                        track actual expenses, and see exactly how much money
                        you have remaining.
                    </p>

                    <p class="text-secondary">
                        Whether you're tracking groceries, housing, transportation,
                        or entertainment, Mintly helps you stay organized
                        and make informed spending decisions throughout the month.
                    </p>

                    <a href="{{ route('register') }}"
                       class="btn btn-primary btn-lg mt-3">
                        Start Tracking Your Budget
                    </a>

                </div>

                <div class="col-lg-6">

                    <img src="{{ asset('images/budget-tracker.png') }}"
                         class="img-fluid rounded-4 shadow"
                         alt="Mintly Budget Tracker showing monthly category budgets, allocated spending, paid expenses, remaining balances, and progress bars"
                         width="1020">

                </div>

            </div>

        </div>
    </section>

    {{-- Features --}}
    <section class="py-5">
        <div class="container">

            <h2 class="fw-bold mb-4">
                Know Exactly Where Your Budget Stands
            </h2>

            <div class="row g-4">

                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm p-4">

                        <h3 class="h5 fw-bold">
                            Set Category Budgets
                        </h3>

                        <p class="text-secondary mb-0">
                            Create monthly budgets for groceries,
                            dining, entertainment, transportation,
                            housing, and other spending categories.
                            Decide how much you want to spend
                            before the month begins.
                        </p>

                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm p-4">

                        <h3 class="h5 fw-bold">
                            Track Actual Spending
                        </h3>

                        <p class="text-secondary mb-0">
                            Compare your planned budget against
                            actual paid expenses.
                            See how much you've spent
                            in each category throughout the month.
                        </p>

                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm p-4">

                        <h3 class="h5 fw-bold">
                            Monitor Remaining Balances
                        </h3>

                        <p class="text-secondary mb-0">
                            Instantly see how much money remains
                            in each spending category.
                            Visual progress bars help you understand
                            when you're approaching your budget limit.
                        </p>

                    </div>
                </div>

            </div>

        </div>
    </section>

    {{-- Additional Features --}}
    <section class="py-5 bg-white border-top">
        <div class="container">

            <h2 class="fw-bold mb-4">
                Make Monthly Budgeting Easier
            </h2>

            <div class="row g-4">

                <div class="col-md-6">

                    <h3 class="h5 fw-bold">
                        Generate Monthly Budgets
                    </h3>

                    <p class="text-secondary">
                        Use your previous month's transactions
                        to generate category budgets.
                        Start with your actual spending history
                        instead of creating every budget manually.
                    </p>

                </div>

                <div class="col-md-6">

                    <h3 class="h5 fw-bold">
                        Copy Budgets to Next Month
                    </h3>

                    <p class="text-secondary">
                        Keep your budgeting routine consistent
                        by copying your current category budgets
                        into the following month.
                        Make adjustments whenever your financial
                        priorities change.
                    </p>

                </div>

                <div class="col-md-6">

                    <h3 class="h5 fw-bold">
                        Visual Spending Progress
                    </h3>

                    <p class="text-secondary">
                        Follow your spending with easy-to-read
                        progress bars showing how much of each
                        category budget has been used.
                    </p>

                </div>

                <div class="col-md-6">

                    <h3 class="h5 fw-bold">
                        Planned vs. Actual
                    </h3>

                    <p class="text-secondary">
                        Compare your monthly spending targets
                        with actual expenses to identify
                        opportunities to improve your
                        budgeting habits.
                    </p>

                </div>

            </div>

        </div>
    </section>

    {{-- Call To Action --}}
    <section class="py-5 text-center">
        <div class="container">

            <h2 class="fw-bold mb-3">
                Build Better Spending Habits with Mintly
            </h2>

            <p class="lead text-secondary mb-4">
                Budgeting shouldn't feel restrictive.
                Mintly gives you the tools to understand your spending,
                make adjustments, and stay in control of your finances.
            </p>

            <a href="{{ route('register') }}"
               class="btn btn-primary btn-lg">
                Create Your Free Account
            </a>

            <p class="text-secondary small mt-3">
                Free during early access. No credit card required.
            </p>

        </div>
    </section>

@endsection
