<!doctype html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- SEO --}}
    <title>@yield('title') | Mintly Budget</title>
    <meta name="description" content="@yield('description', 'Mintly helps you plan your monthly budget, track expenses, and manage your cash flow.')">
    <link rel="canonical" href="@yield('canonical', url()->current())">
    <meta name="robots" content="index, follow">

    {{-- Open Graph --}}
    <meta property="og:title" content="@yield('title') | Mintly Budget">
    <meta property="og:description" content="@yield('description', 'Mintly helps you plan your monthly budget, track expenses, and manage your cash flow.')">
    <meta property="og:url" content="@yield('canonical', url()->current())">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Mintly">
    <meta property="og:locale" content="en_US">
    <meta property="og:image" content="{{ secure_asset('images/social-preview-v1.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    {{-- Twitter --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title') | Mintly Budget">
    <meta name="twitter:description" content="@yield('description')">
    <meta name="twitter:image" content="{{ secure_asset('images/social-preview-v1.png') }}">
    <meta name="twitter:site" content="@waltprorok">

    {{-- Icons --}}
    <link rel="apple-touch-icon" sizes="180x180" href="{{ secure_asset('images/apple-touch-icon.png') }}">
    <meta name="theme-color" content="#080d0c">
    <meta name="google-site-verification" content="RPPhABGHaCi4Qmbse4behpD6u3I7nrE7nxuVnXzE3mA">

    @vite(['resources/css/bootstrap.css', 'resources/js/app.js'])
</head>
<body>

{{-- Navigation --}}
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ url('/') }}">
            Mintly Budget
        </a>

        <button class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#mainNav"
                aria-controls="mainNav"
                aria-expanded="false"
                aria-label="Toggle navigation menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/#how-it-works') }}">
                        How it works
                    </a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle"
                       href="#"
                       id="featuresDropdown"
                       role="button"
                       data-bs-toggle="dropdown"
                       aria-expanded="false">
                        Features
                    </a>

                    <ul class="dropdown-menu" aria-labelledby="featuresDropdown">
                        <li><a class="dropdown-item" href="{{ url('/#features') }}">All Features</a></li>
{{--                        <li><hr class="dropdown-divider"></li>--}}
                        <li><a class="dropdown-item" href="{{ route('marketing.monthly-budget-planner') }}">Monthly Budget Planner</a></li>
                        <li><a class="dropdown-item" href="{{ route('marketing.expense-tracker') }}">Expense Tracker</a></li>
                        <li><a class="dropdown-item"  href="{{ route('marketing.budget-tracker') }}">Budget Tracker</a></li>
                        <li><a class="dropdown-item" href="{{ route('marketing.cash-flow-planner') }}">Cash Flow Planner</a></li>
                        <li><a class="dropdown-item" href="{{ route('marketing.csv-import') }}">CSV Transaction Import</a></li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/#view-app') }}">
                        View App
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/#pricing') }}">
                        Pricing
                    </a>
                </li>
            </ul>

            <div class="d-flex gap-2">
                @auth
                    <a href="{{ url('/dashboard') }}"
                       class="btn btn-outline-primary">
                        Open App
                    </a>

                    <a class="btn btn-primary"
                       href="{{ route('logout') }}"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        Logout
                    </a>

                    <form id="logout-form"
                          action="{{ route('logout') }}"
                          method="POST"
                          class="d-none">
                        @csrf
                    </form>
                @else
                    <a href="{{ route('login') }}"
                       class="btn btn-sm btn-outline-secondary">
                        Log in
                    </a>

                    <a href="{{ route('register') }}"
                       class="btn btn-sm btn-primary">
                        Get started
                    </a>
                @endauth

            </div>
        </div>
    </div>
</nav>

<main>
    @yield('content')
</main>

{{-- Footer --}}
<footer class="bg-dark text-light pt-5 pb-4">
    <div class="container">
        <div class="row text-start">
            <div class="col-12 col-md-4 mb-4">
                <div class="d-flex align-items-center mb-3">

                    <img src="{{ asset('images/apple-touch-icon.png') }}"
                         alt="Mintly logo"
                         class="me-3 rounded"
                         style="width:60px;height:60px;">

                    <h4 class="mb-0 fw-bold">
                        {{ config('app.name') }}
                    </h4>

                </div>

                <p class="text-secondary">
                    Plan your month, track each week,
                    and stay in control of your money.
                </p>

            </div>

            <div class="col-12 col-md-8">
                <div class="row">
                    <div class="col-6 col-md-4 mb-4">
                        <h6 class="fw-bold mb-3">Features</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <a href="{{ route('marketing.monthly-budget-planner') }}"
                                   class="text-secondary text-decoration-none">
                                    Monthly Budget Planner
                                </a>
                            </li>
                            <li class="mb-2">
                                <a href="{{ route('marketing.expense-tracker') }}"
                                   class="text-secondary text-decoration-none">
                                    Expense Tracker
                                </a>
                            </li>
                            <li class="mb-2">
                                <a href="{{ route('marketing.budget-tracker') }}"
                                   class="text-secondary text-decoration-none">
                                    Budget Tracker
                                </a>
                            </li>
                            <li class="mb-2">
                                <a href="{{ route('marketing.cash-flow-planner') }}"
                                   class="text-secondary text-decoration-none">
                                    Cash Flow Planner
                                </a>
                            </li>
                            <li class="mb-2">
                                <a href="{{ route('marketing.csv-import') }}"
                                   class="text-secondary text-decoration-none">
                                    CSV Import
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="col-6 col-md-4 mb-4">
                        <h6 class="fw-bold mb-3">Links</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <a href="https://mintlybudget.blogspot.com/"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="text-secondary text-decoration-none">
                                    Blog
                                </a>
                            </li>

                            <li><a href="{{ route('login') }}"
                                   class="text-secondary text-decoration-none">
                                    Log in
                                </a>
                            </li>

                        </ul>

                    </div>

                    <div class="col-12 col-md-4 mb-4">
                        <h6 class="fw-bold mb-3">Legal</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <a href="{{ route('terms') }}"
                                   class="text-secondary text-decoration-none">
                                    Terms Of Service
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('privacy') }}"
                                   class="text-secondary text-decoration-none">
                                    Privacy Policy
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-3">
            <h6 class="fw-semibold mb-1">
                {{ config('app.name') }}
            </h6>

            <p class="small text-secondary mb-0">
                © {{ date('Y') }} {{ config('app.name') }}.
                All rights reserved.
            </p>
        </div>
    </div>
</footer>

</body>
</html>
