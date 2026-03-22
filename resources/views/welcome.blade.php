<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mintly — Monthly Cash Flow Made Clear</title>
    <meta name="description" content="Plan your month, track your spending, and understand your cash flow week by week with Mintly.">

    @vite(['resources/css/bootstrap.css', 'resources/js/app.js'])
</head>
<body class="bg-light">

{{-- Top Nav --}}
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ url('/') }}">
            Mintly Budget
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                <li class="nav-item"><a class="nav-link" href="#how-it-works">How it works</a></li>
                <li class="nav-item"><a class="nav-link" href="#pricing">Pricing</a></li>
                <li class="nav-item"><a class="nav-link" href="#faq">FAQ</a></li>
            </ul>

            <div class="d-flex gap-2">
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn btn-outline-primary">Open App</a>

                    <a class="btn btn-primary"
                       href="{{ route('logout') }}"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        Logout
                    </a>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-primary">Log in</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">Get started</a>
                @endauth
            </div>
        </div>
    </div>
</nav>

{{-- Hero --}}
<header class="py-5 bg-white border-bottom">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-6">
                <span class="badge bg-primary-subtle text-primary mb-3">Mintly • Cash Flow Planner</span>

                <h1 class="display-5 fw-bold lh-1 mb-3">
                    Take control of your money — one week at a time.
                </h1>

                <p class="lead text-secondary mb-4">
                    Mintly helps you plan your month, track spending, and understand your cash flow in real time.
                    See what’s coming, what’s paid, and what’s left — all in one clear dashboard.
                </p>

                <div class="d-flex flex-wrap gap-2 mb-3">
                    <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Create your account</a>
                    <a href="#features" class="btn btn-outline-secondary btn-lg">See features</a>
                </div>

                <div class="d-flex flex-wrap gap-3 text-secondary small">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill bg-success-subtle text-success">✓</span>
                        <span>Weekly cash flow tracking</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill bg-success-subtle text-success">✓</span>
                        <span>Bill & expense tracking</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill bg-success-subtle text-success">✓</span>
                        <span>Clear monthly overview</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <div class="fw-bold">Monthly Snapshot</div>
                                <div class="text-secondary small">{{ now()->format('F') }} • Cash flow overview</div>
                            </div>
                            <span class="badge bg-dark-subtle text-dark">Preview</span>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <div class="p-3 rounded bg-light border">
                                    <div class="text-secondary small">Income</div>
                                    <div class="h5 mb-0">$4,725</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 rounded bg-light border">
                                    <div class="text-secondary small">Expenses</div>
                                    <div class="h5 mb-0">$3,625</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="p-3 rounded bg-light border">
                                    <div class="text-secondary small">Net Remaining</div>
                                    <div class="h4 mb-0 text-success">$1,099</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between small text-secondary mb-2">
                                <span>Top spending categories</span>
                                <span>This month</span>
                            </div>
                            <div class="list-group">
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Mortgage</span><span class="fw-semibold">$1,839</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Groceries</span><span class="fw-semibold">$595</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Subscriptions</span><span class="fw-semibold">$99</span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="{{ route('login') }}" class="btn btn-outline-primary w-100">Log in</a>
                            <a href="{{ route('register') }}" class="btn btn-primary w-100">Start free</a>
                        </div>
                    </div>
                </div>

                <p class="text-center text-secondary small mt-3 mb-0">
                    “I finally understand my monthly cash flow.” — A Mintly user
                </p>
            </div>
        </div>
    </div>
</header>

{{-- Features --}}
<section id="features" class="py-5">
    <div class="container">
        <h2 class="fw-bold">Built for real-life budgeting</h2>
        <p class="text-secondary mb-4">
            Stay on top of your money without spreadsheets or confusion.
        </p>

        <div class="row g-3">
            <div class="col-md-6 col-lg-4">
                <div class="card p-4 shadow-sm border-0">
                    <div class="fw-bold mb-2">Monthly Overview</div>
                    <div class="text-secondary">
                        Instantly see your income, expenses, and net for the month.
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card p-4 shadow-sm border-0">
                    <div class="fw-bold mb-2">Weekly Cash Flow</div>
                    <div class="text-secondary">
                        Break your month into weeks so you always know where you stand.
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card p-4 shadow-sm border-0">
                    <div class="fw-bold mb-2">Bill Tracking</div>
                    <div class="text-secondary">
                        Track what’s paid and what’s still outstanding.
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card p-4 shadow-sm border-0">
                    <div class="fw-bold mb-2">Default Categories</div>
                    <div class="text-secondary">
                        Start with ready-made categories for income and expenses —
                        so you can begin tracking your money in minutes
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card p-4 shadow-sm border-0">
                    <div class="fw-bold mb-2">Visual Reports</div>
                    <div class="text-secondary">
                        See exactly where your money goes with clear charts and category breakdowns.
                        Instantly understand your spending patterns and make smarter decisions each month.
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card p-4 shadow-sm border-0">
                    <div class="fw-bold mb-2">Prepare Next Month</div>
                    <div class="text-secondary">
                        Roll over recurring income and expenses into the next month automatically —
                        no duplicates, no manual setup. Stay organized and ready ahead of time.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Pricing --}}
<section id="pricing" class="py-5 bg-white border-top">
    <div class="container">
        <div class="row mb-4">
            <div class="col-lg-8">
                <h2 class="fw-bold">Simple pricing</h2>
                <p class="text-secondary mb-0">Start free, upgrade when you’re ready.</p>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="fw-bold">Free</div>
                        <div class="display-6 fw-bold my-2">$0</div>
                        <div class="text-secondary mb-3">Basic budgeting for getting started.</div>
                        <ul class="text-secondary">
                            <li>Categories</li>
                            <li>Transactions</li>
                            <li>Monthly summary</li>
                        </ul>
                        <a href="{{ route('register') }}" class="btn btn-outline-primary w-100">Start free</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <span class="badge bg-primary mb-2">Most popular</span>
                        <div class="fw-bold">Pro</div>
                        <div class="display-6 fw-bold my-2">$8<span class="fs-6 text-secondary">/mo</span></div>
                        <div class="text-secondary mb-3">For people serious about goals.</div>
                        <ul class="text-secondary">
                            <li>Goals & savings</li>
                            <li>Advanced reporting</li>
                            <li>Exports</li>
                        </ul>
                        <a href="{{ route('register') }}" class="btn btn-primary w-100">Go Pro</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="fw-bold">Team</div>
                        <div class="display-6 fw-bold my-2">$19<span class="fs-6 text-secondary">/mo</span></div>
                        <div class="text-secondary mb-3">For families or shared budgets.</div>
                        <ul class="text-secondary">
                            <li>Multiple members</li>
                            <li>Permissions</li>
                            <li>Shared categories</li>
                        </ul>
                        <a href="{{ route('register') }}" class="btn btn-outline-primary w-100">Start Team</a>
                    </div>
                </div>
            </div>
        </div>

        <p class="text-secondary small mt-3 mb-0">
            (Pricing is placeholder — wire it to Stripe/Cashier when you’re ready.)
        </p>
    </div>
</section>

{{-- FAQ --}}
<section id="faq" class="py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-lg-8">
                <h2 class="fw-bold">FAQ</h2>
                <p class="text-secondary mb-0">Everything you need to know about Mintly.</p>
            </div>
        </div>

        <div class="accordion" id="faqAccordion">

            <div class="accordion-item">
                <h2 class="accordion-header" id="q1">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#a1">
                        How is Mintly different from other budget apps?
                    </button>
                </h2>
                <div id="a1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                    <div class="accordion-body text-secondary">
                        Mintly focuses on <strong>monthly cash flow</strong>, not just tracking spending.
                        You can see how your income and expenses affect each week, track what’s already paid,
                        and understand what’s left before the month ends.
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="q2">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#a2">
                        Do I have to connect my bank account?
                    </button>
                </h2>
                <div id="a2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body text-secondary">
                        No. Mintly is designed to be simple and flexible — you can manually add transactions,
                        giving you full control without needing to connect external accounts.
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="q3">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#a3">
                        Can I track bills and see what’s unpaid?
                    </button>
                </h2>
                <div id="a3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body text-secondary">
                        Yes. Mintly lets you mark transactions as paid and clearly shows outstanding amounts,
                        so you always know what’s left to cover each month.
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="q4">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#a4">
                        How does the weekly breakdown work?
                    </button>
                </h2>
                <div id="a4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body text-secondary">
                        Your income and expenses are automatically grouped by week, so you can see how each week
                        impacts your overall budget — helping you avoid running out of money before the month ends.
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="q5">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#a5">
                        What kind of insights can I see?
                    </button>
                </h2>
                <div id="a5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body text-secondary">
                        Mintly shows category breakdowns, monthly trends, and income vs expenses over time —
                        so you can quickly understand where your money is going and make better decisions.
                    </div>
                </div>
            </div>

        </div>

        <div class="text-center mt-4">
            <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Get started with Mintly</a>
            <div class="text-secondary small mt-2">Create an account in under a minute.</div>
        </div>
    </div>
</section>

{{-- Footer --}}
<footer class="py-4">
    <div class="container d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="text-secondary small">
            © {{ date('Y') }} Mintly. All rights reserved.
        </div>
        <div class="d-flex gap-3 small">
            <a class="text-decoration-none" href="#features">Features</a>
            <a class="text-decoration-none" href="#pricing">Pricing</a>
            <a class="text-decoration-none" href="#faq">FAQ</a>
            <a class="text-decoration-none" href="{{ route('login') }}">Log in</a>
            <a class="text-decoration-none" href="{{ route('terms-conditions') }}">Terms & Conditions</a>
            <a class="text-decoration-none" href="{{ route('user-agreement') }}">User Agreement</a>
        </div>
    </div>
</footer>

<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
