@extends('layouts.app')
@section('title', 'Terms and Conditions')
@section('content')

    <div class="container py-5">
        <div class="bg-white p-5 rounded shadow-sm">

            <h1 class="fw-bold mb-4">Terms and Conditions</h1>
            <p class="text-secondary">Last updated: {{ date('F Y') }}</p>

            <hr class="my-4">

            <h4 class="fw-bold mt-4">1. Agreement to Terms</h4>
            <p>
                By accessing or using Mintly (“the Service”), you agree to comply with and be bound by these Terms and Conditions.
                If you do not agree, you may not use the Service.
            </p>

            <h4 class="fw-bold mt-4">2. Use of the Service</h4>
            <p>
                Mintly provides tools for tracking income, expenses, and monthly cash flow. You agree to use the Service only
                for lawful purposes and in accordance with these terms.
            </p>

            <ul>
                <li>You may not use the Service for illegal or fraudulent activities.</li>
                <li>You may not attempt to interfere with or disrupt the platform.</li>
                <li>You are responsible for all activity under your account.</li>
            </ul>

            <h4 class="fw-bold mt-4">3. Account Registration</h4>
            <p>
                To use certain features, you must create an account. You agree to provide accurate information and keep your
                login credentials secure. You are responsible for any activity that occurs under your account.
            </p>

            <h4 class="fw-bold mt-4">4. User Data</h4>
            <p>
                You retain ownership of the financial data you enter into Mintly. By using the Service, you grant us permission
                to store and process this data solely to provide functionality.
            </p>

            <h4 class="fw-bold mt-4">5. Financial Information Disclaimer</h4>
            <p>
                Mintly is a financial tracking tool and does not provide financial, legal, or investment advice.
                All information is provided for informational purposes only.
            </p>

            <h4 class="fw-bold mt-4">6. Payments and Subscriptions</h4>
            <p>
                Certain features may require a paid subscription. Pricing and features are subject to change at any time.
            </p>
            <ul>
                <li>Subscriptions renew automatically unless canceled.</li>
                <li>No refunds are guaranteed unless required by law.</li>
            </ul>

            <h4 class="fw-bold mt-4">7. Service Availability</h4>
            <p>
                We aim to provide reliable service but do not guarantee uninterrupted access. Mintly may be updated,
                modified, or temporarily unavailable without notice.
            </p>

            <h4 class="fw-bold mt-4">8. Termination</h4>
            <p>
                We reserve the right to suspend or terminate accounts that violate these terms or misuse the Service.
            </p>

            <h4 class="fw-bold mt-4">9. Limitation of Liability</h4>
            <p>
                To the fullest extent permitted by law, Mintly is not liable for any indirect, incidental, or consequential damages,
                including financial loss, arising from your use of the Service.
            </p>

            <h4 class="fw-bold mt-4">10. Changes to Terms</h4>
            <p>
                We may update these Terms and Conditions at any time. Continued use of the Service after changes
                constitutes acceptance of the updated terms.
            </p>

            <h4 class="fw-bold mt-4">11. Governing Law</h4>
            <p>
                These terms are governed by the laws of your jurisdiction, without regard to conflict of law principles.
            </p>

            <h4 class="fw-bold mt-4">12. Contact</h4>
            <p>
                For questions regarding these Terms, please contact:
                <br>
                <strong>support@mintlyapp.com</strong>
            </p>

        </div>
    </div>

@endsection

