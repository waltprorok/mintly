@extends('layouts.marketing-feature')
@section('title', 'Weekly Cash Flow Planner')
@section('description', 'Plan your weekly cash flow, track upcoming bills, and understand how income and expenses affect your monthly budget.')
@section('canonical', 'https://mintlybudget.com/cash-flow-planner')
@section('content')

    <section class="py-5 bg-white border-bottom">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">

                <span class="badge bg-success mb-3">
                    Weekly Cash Flow Planning
                </span>

                    <h1 class="display-5 fw-bold mb-4">
                        Know How Much Money You'll Have Left
                    </h1>

                    <p class="lead text-secondary">
                        Plan your finances week by week
                        instead of waiting until the end of the month.
                    </p>

                    <p class="text-secondary">
                        Mintly helps you understand how upcoming
                        income and expenses affect your available
                        money throughout the month.
                    </p>

                    <a href="{{ route('register') }}"
                       class="btn btn-primary btn-lg">
                        Start Planning Cash Flow
                    </a>

                </div>

                <div class="col-lg-6">

                    <img src="{{ asset('images/3-budget-planner-weeks.png') }}"
                         class="img-fluid rounded-4 shadow"
                         alt="Mintly weekly cash flow planner showing income and expenses"
                         width="1020">

                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <h2 class="fw-bold mb-4">
                Understand Your Money Week by Week
            </h2>

            <div class="row g-4">

                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm p-4">

                        <h3 class="h5 fw-bold">Weekly Breakdown</h3>

                        <p class="text-secondary mb-0">
                            View income and expenses grouped
                            by week to understand your cash flow.
                        </p>

                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm p-4">

                        <h3 class="h5 fw-bold">Upcoming Expenses</h3>

                        <p class="text-secondary mb-0">
                            See which bills and expenses
                            are approaching before they are due.
                        </p>

                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm p-4">

                        <h3 class="h5 fw-bold">Monthly Overview</h3>

                        <p class="text-secondary mb-0">
                            Understand how weekly spending
                            affects your overall monthly budget.
                        </p>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-white border-top">
        <div class="container">
            <h2 class="fw-bold mb-3">
                Stop Guessing Between Paychecks
            </h2>

            <p class="text-secondary">
                A monthly budget tells you how much money
                you expect to earn and spend.
                Weekly cash flow planning helps you understand
                when that money is coming in and going out.
            </p>

            <p class="text-secondary">
                By reviewing upcoming bills alongside expected income,
                you can make more informed spending decisions
                before your next paycheck arrives.
            </p>

            <a href="{{ route('register') }}" class="btn btn-primary">
                Create Your Free Account
            </a>
        </div>
    </section>

@endsection
