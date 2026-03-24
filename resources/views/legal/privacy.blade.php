@extends('layouts.app')
@section('title', 'Privacy Policy')
@section('content')

    <div class="container py-5">
        <div class="bg-white p-5 rounded shadow-sm">

            <h1 class="fw-bold mb-4">Privacy Policy</h1>
            <p class="text-secondary">Last updated: {{ date('F Y') }}</p>

            <hr class="my-4">

            <h4 class="fw-bold mt-4">1. Information We Collect</h4>
            <p>
                We collect information you provide directly when using Mintly (“the Service”), including:
            </p>
            <ul>
                <li>Name and email address</li>
                <li>Financial data you enter, such as transactions, categories, and budgets</li>
                <li>Recurring transaction data</li>
                <li>Optional notes or descriptions</li>
            </ul>
            <p>
                Mintly does not connect to your bank accounts or automatically import financial data.
            </p>

            <h4 class="fw-bold mt-4">2. How We Use Your Information</h4>
            <p>We use your information to:</p>
            <ul>
                <li>Provide budgeting and financial tracking features</li>
                <li>Generate reports and insights</li>
                <li>Prepare future monthly data based on recurring transactions</li>
                <li>Send account-related and optional monthly summary emails</li>
                <li>Improve the performance and functionality of the Service</li>
            </ul>

            <h4 class="fw-bold mt-4">3. Data Ownership</h4>
            <p>
                Your financial data belongs to you. We do not sell, rent, or share your personal financial data with third parties.
            </p>

            <h4 class="fw-bold mt-4">4. Data Storage and Security</h4>
            <p>
                We take reasonable steps to protect your data using industry-standard security measures, including encrypted connections
                and secure authentication. However, no system is completely secure.
            </p>

            <h4 class="fw-bold mt-4">5. Emails and Notifications</h4>
            <p>
                We may send you emails related to your account, including monthly financial summaries and important updates.
                You can opt out of non-essential emails at any time.
            </p>

            <h4 class="fw-bold mt-4">6. Third-Party Services</h4>
            <p>
                We may use third-party services (such as hosting providers and email delivery services) to operate the Service.
                These providers only access your data as necessary to perform their functions.
            </p>

            <h4 class="fw-bold mt-4">7. Your Rights</h4>
            <p>You may:</p>
            <ul>
                <li>Access and update your account information</li>
                <li>Delete your account and associated data</li>
            </ul>

            <h4 class="fw-bold mt-4">8. Data Retention</h4>
            <p>
                If you delete your account, your data (including transactions, categories, and related financial information)
                will be permanently deleted from our systems within a reasonable timeframe.
            </p>

            <h4 class="fw-bold mt-4">9. Changes to This Policy</h4>
            <p>
                We may update this Privacy Policy from time to time. Continued use of the Service after changes means you accept
                the updated policy.
            </p>

            <h4 class="fw-bold mt-4">10. Contact</h4>
            <p>
                If you have questions about this Privacy Policy, contact us at:
                <br>
                <strong>Email:</strong> support@mintly.app
            </p>

        </div>
    </div>

@endsection
