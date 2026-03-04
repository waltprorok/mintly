<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mintly — Personal Budget Tracker</title>

    @vite(['resources/css/bootstrap.css', 'resources/js/app.js'])
    <meta name="description" content="Mintly is a personal budget tracker to plan, track, and grow your money with clarity.">
</head>
<body class="bg-light">

{{-- Top Nav --}}
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ url('/') }}">
            Mintly
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                <li class="nav-item"><a class="nav-link" href="#how-it-works">How it works</a></li>
                <li class="nav-item"><a class="nav-link" href="#pricing">Pricing</a></li>
                <li class="nav-item"><a class="nav-link" href="#faq">FAQ</a></li>
            </ul>

            <div class="d-flex gap-2">
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn btn-outline-primary">Open App</a>

                    {{-- Optional: Filament admin shortcut (only show if you want) --}}
{{--                    <a href="{{ url('/admin') }}" class="btn btn-outline-secondary">Admin</a>--}}

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
                <span class="badge bg-primary-subtle text-primary mb-3">Mintly • Personal Budget Tracker</span>

                <h1 class="display-5 fw-bold lh-1 mb-3">
                    Budgeting that feels simple — not stressful.
                </h1>

                <p class="lead text-secondary mb-4">
                    Mintly helps you plan your spending, track transactions, and see your progress in one clean dashboard.
                    Build habits, hit goals, and stay in control of your money.
                </p>

                <div class="d-flex flex-wrap gap-2 mb-3">
                    <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Create your account</a>
                    <a href="#features" class="btn btn-outline-secondary btn-lg">See features</a>
                </div>

                <div class="d-flex flex-wrap gap-3 text-secondary small">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill bg-success-subtle text-success">✓</span>
                        <span>Fast setup</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill bg-success-subtle text-success">✓</span>
                        <span>Clear categories</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill bg-success-subtle text-success">✓</span>
                        <span>Goal tracking</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                {{-- Fake "app preview" card --}}
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <div class="fw-bold">Monthly Snapshot</div>
                                <div class="text-secondary small">March • Budget overview</div>
                            </div>
                            <span class="badge bg-dark-subtle text-dark">Preview</span>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <div class="p-3 rounded bg-light border">
                                    <div class="text-secondary small">Income</div>
                                    <div class="h5 mb-0">$4,800</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 rounded bg-light border">
                                    <div class="text-secondary small">Spent</div>
                                    <div class="h5 mb-0">$3,150</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="p-3 rounded bg-light border">
                                    <div class="text-secondary small">Remaining</div>
                                    <div class="h4 mb-0 text-success">$1,650</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between small text-secondary mb-2">
                                <span>Spending by category</span>
                                <span>Top 3</span>
                            </div>
                            <div class="list-group">
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Rent</span><span class="fw-semibold">$1,600</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Groceries</span><span class="fw-semibold">$420</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Dining</span><span class="fw-semibold">$310</span>
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
                    “I finally know where my money goes.” — A Mintly user
                </p>
            </div>
        </div>
    </div>
</header>

{{-- Features --}}
<section id="features" class="py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-lg-8">
                <h2 class="fw-bold">Everything you need to stay on budget</h2>
                <p class="text-secondary mb-0">
                    Track spending, set goals, and build confidence with a dashboard that makes sense.
                </p>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="fw-bold mb-2">Smart Categories</div>
                        <div class="text-secondary">
                            Organize spending with categories that match real life: rent, groceries, subscriptions, and more.
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="fw-bold mb-2">Budget Targets</div>
                        <div class="text-secondary">
                            Set monthly limits by category and get a clear view of what’s left before you overspend.
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="fw-bold mb-2">Goals & Savings</div>
                        <div class="text-secondary">
                            Track goals like emergency funds or a vacation, and measure progress automatically.
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="fw-bold mb-2">Quick Add Transactions</div>
                        <div class="text-secondary">
                            Add spending in seconds so your budget stays up to date without extra friction.
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="fw-bold mb-2">Simple Reports</div>
                        <div class="text-secondary">
                            See trends across weeks and months to spot patterns and make better decisions.
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="fw-bold mb-2">Private by Default</div>
                        <div class="text-secondary">
                            Your budget is yours. Keep things clean, minimal, and secure.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- How it works --}}
<section id="how-it-works" class="py-5 bg-white border-top border-bottom">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-6">
                <h2 class="fw-bold">How Mintly works</h2>
                <p class="text-secondary">
                    A simple loop: plan → track → improve. The dashboard keeps you honest and motivated.
                </p>

                <div class="d-grid gap-3">
                    <div class="p-3 rounded border bg-light">
                        <div class="fw-bold">1) Set your monthly budget</div>
                        <div class="text-secondary small">Define category limits that fit your life.</div>
                    </div>
                    <div class="p-3 rounded border bg-light">
                        <div class="fw-bold">2) Track spending as you go</div>
                        <div class="text-secondary small">Log transactions quickly and stay current.</div>
                    </div>
                    <div class="p-3 rounded border bg-light">
                        <div class="fw-bold">3) Review progress and adjust</div>
                        <div class="text-secondary small">See trends and move money where it matters.</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="fw-bold mb-2">Your dashboard, at a glance</div>
                        <p class="text-secondary mb-0">
                            Know what’s left, what’s coming up, and what changed — without digging through spreadsheets.
                        </p>
                    </div>
                </div>

{{--                <div class="alert alert-primary mt-3 mb-0">--}}
{{--                    Want admin tools? Filament is available at <strong>/admin</strong> for your staff/admin accounts.--}}
{{--                </div>--}}
            </div>
        </div>
    </div>
</section>

{{-- Pricing --}}
<section id="pricing" class="py-5">
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
<section id="faq" class="py-5 bg-white border-top">
    <div class="container">
        <div class="row mb-4">
            <div class="col-lg-8">
                <h2 class="fw-bold">FAQ</h2>
                <p class="text-secondary mb-0">A few quick answers.</p>
            </div>
        </div>

        <div class="accordion" id="faqAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header" id="q1">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#a1">
                        Does Mintly replace my bank app?
                    </button>
                </h2>
                <div id="a1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                    <div class="accordion-body text-secondary">
                        Mintly is focused on budgeting and clarity. You can track what matters and understand your habits.
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="q2">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#a2">
                        Is Filament the user dashboard?
                    </button>
                </h2>
                <div id="a2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body text-secondary">
                        Typically no — Filament is best as an admin/staff panel. Your public users should use the app dashboard.
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="q3">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#a3">
                        Can I add subscriptions later?
                    </button>
                </h2>
                <div id="a3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body text-secondary">
                        Yes. Laravel Cashier + Stripe is a common setup for SaaS billing.
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
        </div>
    </div>
</footer>

{{-- If you're using Laravel UI, Bootstrap JS is usually in app.js --}}
{{--<script src="{{ asset('js/app.js') }}"></script>--}}
</body>
</html>
