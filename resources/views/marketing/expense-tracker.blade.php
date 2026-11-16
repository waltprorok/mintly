@extends('layouts.marketing-feature')
@section('title', 'Free Monthly Expense Tracker')
@section('description', 'Track monthly expenses, organize spending categories, and understand where your money goes with Mintly Budget.')
@section('canonical', 'https://mintlybudget.com/expense-tracker')
@section('content')

    <section class="py-5 bg-white border-bottom">
        <div class="container">

            <div class="row align-items-center g-5">

                <div class="col-lg-6">

                <span class="badge bg-success mb-3">
                    Expense Tracking
                </span>

                    <h1 class="display-5 fw-bold mb-4">
                        Track Your Monthly Expenses
                    </h1>

                    <p class="lead text-secondary">
                        Understand exactly where your money goes
                        with Mintly's monthly expense tracker.
                    </p>

                    <p class="text-secondary">
                        Organize transactions by category, monitor
                        spending patterns, and identify opportunities
                        to improve your financial habits.
                    </p>

                    <a href="{{ route('register') }}"
                       class="btn btn-primary btn-lg">
                        Start Tracking Expenses
                    </a>

                </div>

                <div class="col-lg-6">

                    <img src="{{ asset('images/5-transactions.png') }}"
                         class="img-fluid rounded-4 shadow"
                         alt="Mintly expense tracker showing monthly transactions"
                         width="1020">
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">

            <h2 class="fw-bold mb-4">
                Understand Your Spending Habits
            </h2>

            <div class="row g-4">

                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm p-4">

                        <h3 class="h5 fw-bold">Organize Transactions</h3>

                        <p class="text-secondary mb-0">
                            Keep track of purchases, bills, and
                            other expenses throughout the month.
                        </p>

                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm p-4">

                        <h3 class="h5 fw-bold">Spending Categories</h3>

                        <p class="text-secondary mb-0">
                            Categorize expenses such as groceries,
                            housing, transportation, and entertainment.
                        </p>

                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm p-4">

                        <h3 class="h5 fw-bold">Visual Reports</h3>

                        <p class="text-secondary mb-0">
                            Review spending charts and category
                            breakdowns to identify financial trends.
                        </p>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-white border-top">
        <div class="container">
            <h2 class="fw-bold mb-3">
                Make Better Spending Decisions
            </h2>

            <p class="text-secondary">
                Tracking expenses is the first step toward understanding
                your financial habits. Mintly gives you a clear view
                of spending without requiring complicated spreadsheets.
            </p>

            <p class="text-secondary">
                Review your transactions regularly to identify
                unnecessary expenses and make adjustments
                that support your financial goals.
            </p>

            <a href="{{ route('register') }}" class="btn btn-primary">
                Create Your Free Account
            </a>
        </div>
    </section>

@endsection
