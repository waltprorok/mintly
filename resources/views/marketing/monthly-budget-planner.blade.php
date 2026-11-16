@extends('layouts.marketing-feature')
@section('title', 'Free Monthly Budget Planner')
@section('description', 'Create a monthly budget, track income and expenses, manage recurring bills, and plan ahead with Mintly Budget.')
@section('canonical', 'https://mintlybudget.com/monthly-budget-planner')
@section('content')

    <section class="py-5 bg-white border-bottom">
        <div class="container">

            <div class="row align-items-center g-5">

                <div class="col-lg-6">

                <span class="badge bg-success mb-3">
                    Monthly Budget Planning
                </span>

                    <h1 class="display-5 fw-bold mb-4">
                        Free Monthly Budget Planner
                    </h1>

                    <p class="lead text-secondary">
                        Take control of your monthly finances with Mintly Budget.
                        Organize income, track expenses, manage recurring bills,
                        and understand how much money you have remaining.
                    </p>

                    <p class="text-secondary">
                        Whether you're building your first budget or improving
                        your spending habits, Mintly makes monthly budgeting
                        simple and easy to maintain.
                    </p>

                    <a href="{{ route('register') }}"
                       class="btn btn-primary btn-lg mt-3">
                        Start Your Free Budget
                    </a>

                </div>

                <div class="col-lg-6">

                    <img src="{{ asset('images/2-budget-planner.png') }}"
                         class="img-fluid rounded-4 shadow"
                         alt="Mintly monthly budget planner showing income and expenses"
                         width="1020">

                </div>

            </div>

        </div>
    </section>

    <section class="py-5">
        <div class="container">

            <h2 class="fw-bold mb-4">
                How Mintly Helps You Plan Your Monthly Budget
            </h2>

            <div class="row g-4">

                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm p-4">

                        <h3 class="h5 fw-bold">Track Income</h3>

                        <p class="text-secondary mb-0">
                            Organize paychecks and other income sources
                            so you know how much money is available each month.
                        </p>

                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm p-4">

                        <h3 class="h5 fw-bold">Manage Expenses</h3>

                        <p class="text-secondary mb-0">
                            Track monthly bills, spending categories,
                            and recurring expenses in one place.
                        </p>

                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm p-4">

                        <h3 class="h5 fw-bold">Prepare Next Month</h3>

                        <p class="text-secondary mb-0">
                            Carry recurring transactions forward
                            without rebuilding your budget from scratch.
                        </p>

                    </div>
                </div>

            </div>

        </div>
    </section>

    <section class="py-5 bg-white border-top">
        <div class="container">

            <h2 class="fw-bold mb-3">
                Build a Budget You Can Actually Maintain
            </h2>

            <p class="text-secondary">
                Start by adding your monthly income and essential expenses.
                Review your spending categories and identify recurring bills.
                As transactions are paid, update their status to keep
                your monthly budget organized.
            </p>

            <p class="text-secondary">
                Mintly helps you understand your finances without
                forcing you into restrictive spending rules.
            </p>

            <a href="{{ route('register') }}" class="btn btn-primary">
                Create Your Free Account
            </a>

        </div>
    </section>

@endsection
