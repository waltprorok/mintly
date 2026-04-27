@extends('layouts.app')
@section('title', 'Terms of Service')
@section('content')

    <div class="container py-5">
        <div class="bg-white p-5 rounded shadow-sm">

            <h1 class="fw-bold mb-4">Terms of Service</h1>
            <p class="text-secondary">Last updated: {{ date('F Y') }}</p>

            <hr class="my-4">

            <h4 class="fw-bold mt-4">1. Agreement to Terms</h4>
            <p>
                By accessing or using Mintly (“the Service”), you agree to be bound by these Terms of Service.
                If you do not agree to these terms, you may not use the Service.
            </p>

            <h4 class="fw-bold mt-4">2. Description of Service</h4>
            <p>
                Mintly is a financial tracking and budgeting tool designed to help users monitor income,
                expenses, and monthly cash flow. The Service provides insights based solely on user-entered data.
            </p>

            <h4 class="fw-bold mt-4">3. Account Registration</h4>
            <p>
                To access certain features, you must create an account. You agree to provide accurate and complete
                information and to keep your login credentials secure. You are responsible for all activity that
                occurs under your account.
            </p>

            <h4 class="fw-bold mt-4">4. User Responsibilities</h4>
            <p>You agree to use the Service only for lawful purposes and in accordance with these terms.</p>
            <ul>
                <li>You may not use the Service for illegal or fraudulent activities.</li>
                <li>You may not attempt to gain unauthorized access to the Service.</li>
                <li>You may not interfere with or disrupt the platform.</li>
                <li>You are responsible for the accuracy of the data you provide.</li>
            </ul>


            <h4 class="fw-bold mt-4">5. User Data</h4>
            <p>
                You retain ownership of the financial data you enter into Mintly. By using the Service, you grant
                us permission to store and process this data solely to provide functionality.
            </p>

            <h4 class="fw-bold mt-4">6. Financial Disclaimer</h4>
            <p>
                Mintly is not a bank, financial advisor, or investment service. The information and insights provided
                are for informational purposes only and should not be considered financial, legal, or investment advice.
                We do not guarantee the accuracy or completeness of any data, calculations, or insights.
                You are solely responsible for your financial decisions.
            </p>


            <h4 class="fw-bold mt-4">7. Payments and Subscriptions</h4>
            <p>
                Certain features may require a paid subscription. Pricing and features are subject to change at any time.
            </p>
            <ul>
                <li>Subscriptions renew automatically unless canceled.</li>
                <li>Refunds are not guaranteed unless required by law.</li>
            </ul>

            <h4 class="fw-bold mt-4">8. Service Availability</h4>
            <p>
                We strive to provide reliable access to the Service but do not guarantee uninterrupted availability.
                The Service may be modified, updated, or temporarily unavailable at any time without notice.
            </p>

            <h4 class="fw-bold mt-4">9. Termination</h4>
            <p>
                We reserve the right to suspend or terminate your account if you violate these terms or misuse the Service.
                You may terminate your account at any time by discontinuing use of the Service or deleting your account.
            </p>


            <h4 class="fw-bold mt-4">10. Limitation of Liability</h4>
            <p>
                To the fullest extent permitted by law, Mintly is provided “as is” without warranties of any kind.
                We are not liable for any indirect, incidental, or consequential damages, including financial loss,
                arising from your use of the Service.
            </p>

            <h4 class="fw-bold mt-4">11. Changes to Terms</h4>
            <p>
                We may update these Terms of Service from time to time. Continued use of the Service after changes
                constitutes acceptance of the updated terms.
            </p>

            <h4 class="fw-bold mt-4">12. Governing Law</h4>
            <p>
                These terms are governed by the laws of the State of Florida, United States, without regard to
                conflict of law principles.
            </p>

            <h4 class="fw-bold mt-4">13. Intellectual Property</h4>
            <p>
                The Service, including all content, features, and functionality, is owned by Mintly and is protected
                by applicable intellectual property laws. You may not copy, modify, distribute, or reverse engineer
                any part of the Service without our permission.
            </p>


            <h4 class="fw-bold mt-4">14. Contact</h4>
            <p>
                If you have questions about these terms, please contact:
                <br>
                <strong>support@mintlybudget.com</strong>
            </p>

        </div>
    </div>

@endsection

