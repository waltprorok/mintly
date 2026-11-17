{{-- Footer --}}
<footer class="bg-dark text-light pt-5 pb-4">
    <div class="container">
        <div class="row text-start">
            <!-- Brand -->
            <div class="col-12 col-md-4 mb-4">
                <div class="d-flex align-items-center justify-content-center justify-content-md-start mb-3">
                    <img src="{{ asset('images/apple-touch-icon.png') }}"
                         alt="Mintly logo"
                         class="me-3 rounded"
                         style="width: 60px; height: 60px;"
                    >
                    <h4 class="mb-0 fw-bold">{{ config('app.name') }}</h4>
                </div>

                <p class="text-secondary mb-3">
                    Plan your month, track each week, and stay in control of your money.
                </p>

                <!-- Social -->
                {{--                <div class="d-flex gap-3 justify-content-center justify-content-md-start">--}}
                {{--                    <a href="#" class="d-flex align-items-center justify-content-center rounded-circle bg-secondary bg-opacity-25 text-light"--}}
                {{--                       style="width:40px; height:40px;">--}}
                {{--                        <i class="bi bi-twitter"></i>--}}
                {{--                    </a>--}}
                {{--                    <a href="#" class="d-flex align-items-center justify-content-center rounded-circle bg-secondary bg-opacity-25 text-light"--}}
                {{--                       style="width:40px; height:40px;">--}}
                {{--                        <i class="bi bi-facebook"></i>--}}
                {{--                    </a>--}}
                {{--                    <a href="#" class="d-flex align-items-center justify-content-center rounded-circle bg-secondary bg-opacity-25 text-light"--}}
                {{--                       style="width:40px; height:40px;">--}}
                {{--                        <i class="bi bi-instagram"></i>--}}
                {{--                    </a>--}}
                {{--                    <a href="mailto:support@mintlybudget.com" class="d-flex align-items-center justify-content-center rounded-circle bg-secondary bg-opacity-25 text-light"--}}
                {{--                       style="width:40px; height:40px;">--}}
                {{--                        <i class="bi bi-envelope"></i>--}}
                {{--                    </a>--}}
                {{--                </div>--}}
            </div>

            <!-- Links Wrapper -->
            <div class="col-12 col-md-8">
                <div class="row">

                    <!-- Features -->
                    <div class="col-6 col-md-4 mb-4">
                        <h6 class="fw-bold mb-3">Features</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <a href="/#how-it-works" class="text-secondary text-decoration-none d-flex align-items-center gap-2">
                                    How it works
                                </a>
                            </li>
                            <li class="mb-2">
                                <a href="/#features" class="text-secondary text-decoration-none d-flex align-items-center gap-2">
                                    Features
                                </a>
                            </li>
                            <li class="mb-2">
                                <a href="/#view-app" class="text-secondary text-decoration-none d-flex align-items-center gap-2">
                                    View App
                                </a>
                            </li>
                            <li class="mb-2">
                                <a href="/#pricing" class="text-secondary text-decoration-none d-flex align-items-center gap-2">
                                    Pricing
                                </a>
                            </li>
                            <li>
                                <a href="/#faq" class="text-secondary text-decoration-none d-flex align-items-center gap-2">
                                    FAQ
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Links -->
                    <div class="col-6 col-md-4 mb-4">
                        <h6 class="fw-bold mb-3">Links</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <a href="https://mintlybudget.blogspot.com/" target="_blank" class="text-secondary text-decoration-none d-flex align-items-center gap-2">
                                    Blog
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('login') }}" class="text-secondary text-decoration-none d-flex align-items-center gap-2">
                                    Log in
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Legal -->
                    <div class="col-12 col-md-4 mb-4">
                        <h6 class="fw-bold mb-3">Legal</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <a href="{{ route('terms') }}" class="text-secondary text-decoration-none d-flex align-items-center gap-2">
                                    Terms Of Service
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('privacy') }}" class="text-secondary text-decoration-none d-flex align-items-center gap-2">
                                    Privacy Policy
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Divider -->
        <hr class="border-secondary opacity-25 d-md-none">

        <!-- Bottom -->
        <div class="text-center mt-3">
            <h6 class="fw-semibold mb-1">{{ config('app.name') }}</h6>
            <p class="small text-secondary mb-0">
                © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </p>
        </div>

    </div>
</footer>
