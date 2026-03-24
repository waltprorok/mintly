<x-filament::page>

    <style>
        .layout {
            display: flex;
            gap: 16px; /* tighter spacing */
            align-items: flex-start;
            max-width: 900px; /* KEY: prevents stretching */
        }

        .left {
            flex: 1;
            max-width: 520px;
        }

        .right {
            flex: 0 0 400px;
        }

        /* Card */
        .results-card {
            width: 100%;
        }

        .row-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .label {
            color: #6b7280;
        }

        .value {
            font-weight: 600;
            font-size: 16px;
            letter-spacing: 0.3px;
        }

        hr {
            border: none;
            border-top: 1px solid #e5e7eb;
        }

        .section-divider {
            margin: 16px 0;
        }

        html.dark .card {
            background: #13151b !important;
            border: 1px solid #1f2937;
            color: #e5e7eb;
        }

        html.dark .right .card {
            background: #13151b !important;
            border: 1px solid #1f2937 !important;
        }

        html.dark .label {
            color: #f8fafc;
        }

        /* VALUES */
        html.dark .value {
            color: #f8fafc;
        }

        html.dark hr {
            border-top: 1px solid #1f2937;
        }

        @media (max-width: 768px) {
            .layout {
                flex-direction: column;
            }

            .right {
                width: 100%;
            }

            .results-card {
                width: 100%;
                margin-top: 16px;
            }
        }
    </style>

    <div class="layout">

        {{-- LEFT: Form --}}
        <div class="left">
            <form wire:submit.prevent="calculate">
                {{ $this->form }}
            </form>
        </div>

        {{-- RIGHT: Results --}}
        <div class="right">
            <div class="card results-card">

                <div class="row-item">
                    <div class="label">Monthly Payment</div>
                    <div class="value">${{ number_format($monthly_payment, 2) }}</div>
                </div>

                @if(($extra_payment ?? 0) > 0)
                    <div class="row-item">
                        <div class="label">Total Monthly Payment</div>
                        <div class="value">
                            ${{ number_format($total_monthly_payment, 2) }}
                        </div>
                    </div>
                @endif

                <hr class="section-divider">

                <div class="row-item">
                    <div class="label">Loan Amount</div>
                    <div class="value">
                        ${{ number_format($loan_amount ?? 0, 2) }}
                    </div>
                </div>

                <div class="row-item">
                    <div class="label">Total Interest</div>
                    <div class="value">${{ number_format($total_interest, 2) }}</div>
                </div>

                <div class="row-item">
                    <div class="label">Total Paid</div>
                    <div class="value">${{ number_format($total_paid, 2) }}</div>
                </div>

                @if($months_saved)
                    <hr class="section-divider">

                    <div class="row-item">
                        <div class="label">Months Saved</div>
                        <div class="value">
                            {{ $months_saved }} months
                        </div>
                    </div>

                    <div class="row-item">
                        <div class="label">Years Saved</div>
                        <div class="value">
                            {{ number_format($years_saved, 1) }} years
                        </div>
                    </div>

                    <div class="row-item">
                        <div class="label">Interest Saved</div>
                        <div class="value">
                            ${{ number_format($interest_saved, 2) }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

</x-filament::page>
