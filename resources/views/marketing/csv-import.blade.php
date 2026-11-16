@extends('layouts.marketing-feature')
@section('title', 'Import Bank CSV Transactions')
@section('description', 'Import bank and credit card CSV transactions into Mintly Budget without connecting your financial accounts.')
@section('canonical', 'https://mintlybudget.com/csv-import')
@section('content')

    <section class="py-5 bg-white border-bottom">
        <div class="container">

            <div class="row align-items-center g-5">

                <div class="col-lg-6">

                <span class="badge bg-success mb-3">
                    CSV Transaction Import
                </span>

                    <h1 class="display-5 fw-bold mb-4">
                        Import Bank Transactions Without Connecting Your Bank
                    </h1>

                    <p class="lead text-secondary">
                        Quickly build your monthly budget
                        by importing transactions from
                        your bank or credit card.
                    </p>

                    <p class="text-secondary">
                        Export your transactions as a CSV file,
                        upload them to Mintly, and review
                        your spending categories and recurring expenses.
                    </p>

                    <a href="{{ route('register') }}"
                       class="btn btn-primary btn-lg">
                        Start Importing Transactions
                    </a>

                </div>

                <div class="col-lg-6">

                    <img src="{{ asset('images/csv-import.png') }}"
                         class="img-fluid rounded-4 shadow"
                         alt="Mintly transaction management interface"
                         width="1020">

                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">

            <h2 class="fw-bold mb-4">
                How CSV Transaction Import Works
            </h2>

            <div class="row g-4">

                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm p-4">

                        <h3 class="h5 fw-bold">1. Export Transactions</h3>

                        <p class="text-secondary mb-0">
                            Download a CSV file containing
                            transactions from your bank
                            or credit card account.
                        </p>

                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm p-4">

                        <h3 class="h5 fw-bold">2. Import Into Mintly</h3>

                        <p class="text-secondary mb-0">
                            Upload your CSV file and
                            import transactions into
                            your monthly budget.
                        </p>

                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm p-4">

                        <h3 class="h5 fw-bold">3. Review Your Budget</h3>

                        <p class="text-secondary mb-0">
                            Review categories, identify
                            recurring expenses, and
                            understand your spending.
                        </p>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-white border-top">
        <div class="container">
            <h2 class="fw-bold mb-3">
                Budget Without Linking Your Bank Account
            </h2>

            <p class="text-secondary">
                Mintly doesn't require a direct bank connection.
                You decide which transactions to import
                and when to update your budget.
            </p>

            <p class="text-secondary">
                CSV importing gives you a convenient way
                to organize financial transactions
                while maintaining control over your budgeting workflow.
            </p>

            <a href="{{ route('register') }}" class="btn btn-primary">
                Create Your Free Account
            </a>
        </div>
    </section>

@endsection
