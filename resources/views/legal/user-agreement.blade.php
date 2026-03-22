@extends('layouts.app')
@section('title', 'User Agreement')
@section('content')

    <div class="container py-5">
        <div class="bg-white p-5 rounded shadow-sm">

            <h1 class="fw-bold mb-4">User Agreement</h1>
            <p class="text-secondary">Last updated: {{ date('F Y') }}</p>

            <hr class="my-4">

            <h4 class="fw-bold mt-4">1. Acceptance of Terms</h4>
            <p>
                By accessing or using Mintly (“the Service”), you agree to be bound by this User Agreement.
                If you do not agree to these terms, you may not use the Service.
            </p>

            <h4 class="fw-bold mt-4">2. Description of Service</h4>
            <p>
                Mintly is a financial planning and budgeting tool designed to help users track income, expenses,
                and monthly cash flow. The Service provides insights based on user-entered data.
            </p>

            <h4 class="fw-bold mt-4">3. User Responsibilities</h4>
            <p>
                You are responsible for the accuracy of the information you enter into Mintly. The Service does not
                verify or guarantee the correctness of your financial data.
            </p>
            <ul>
                <li>You agree to provide accurate and complete information.</li>
                <li>You are responsible for maintaining the confidentiality of your account.</li>
                <li>You agree not to misuse or attempt to disrupt the Service.</li>
            </ul>

            <h4 class="fw-bold mt-4">4. Financial Disclaimer</h4>
            <p>
                Mintly is not a bank, financial advisor, or investment service. The information and insights provided
                are for informational purposes only and should not be considered financial advice.
            </p>
            <p>
                You are solely responsible for your financial decisions.
            </p>

            <h4 class="fw-bold mt-4">5. No Bank Integration</h4>
            <p>
                Mintly does not require or store your bank login credentials. All financial data is entered manually
                or through user-controlled processes. You retain full control over your financial information.
            </p>

            <h4 class="fw-bold mt-4">6. Data and Privacy</h4>
            <p>
                Your data is stored securely and used only to provide the Service. We do not sell your personal data.
                For more details, please refer to our Privacy Policy.
            </p>

            <h4 class="fw-bold mt-4">7. Availability of Service</h4>
            <p>
                We strive to keep Mintly available at all times but do not guarantee uninterrupted access.
                The Service may be modified, suspended, or discontinued at any time without notice.
            </p>

            <h4 class="fw-bold mt-4">8. Limitation of Liability</h4>
            <p>
                Mintly is provided “as is” without warranties of any kind. We are not liable for any financial loss,
                data loss, or damages resulting from the use of the Service.
            </p>

            <h4 class="fw-bold mt-4">9. Termination</h4>
            <p>
                We reserve the right to suspend or terminate your account if you violate this agreement or misuse the Service.
            </p>

            <h4 class="fw-bold mt-4">10. Changes to This Agreement</h4>
            <p>
                We may update this User Agreement from time to time. Continued use of the Service after changes
                constitutes acceptance of the updated terms.
            </p>

            <h4 class="fw-bold mt-4">11. Contact</h4>
            <p>
                If you have questions about this agreement, please contact us at:
                <br>
                <strong>support@mintlyapp.com</strong>
            </p>

        </div>
    </div>

@endsection

