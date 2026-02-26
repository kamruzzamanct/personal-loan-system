@extends('layouts.frontend')

@section('title', 'Personal Loan Application')

@section('content')
    <section class="hero-grid">
        <article class="hero-card">
            <p class="eyebrow">Fast & Secure</p>
            <h1>Personal Loan Application</h1>
            <p>
                Submit your application in minutes. Our system checks affordability and highlights risk indicators
                automatically to improve approval speed and review quality.
            </p>

            <ul id="benefits" class="feature-list">
                <li>Clear validation feedback for every required field.</li>
                <li>Automated risk pre-check based on loan amount and income ratio.</li>
                <li>Secure handling of applicant data with audit-friendly structure.</li>
            </ul>
        </article>

        <article id="application-form" class="form-card">
            <h2>Start Your Application</h2>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-error">
                    <strong>Please fix the following errors:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('loan-applications.store') }}" class="loan-form">
                @csrf

                <div class="field">
                    <label for="first_name">First Name</label>
                    <input id="first_name" name="first_name" type="text" value="{{ old('first_name') }}" required>
                </div>

                <div class="field">
                    <label for="last_name">Last Name</label>
                    <input id="last_name" name="last_name" type="text" value="{{ old('last_name') }}" required>
                </div>

                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required>
                </div>

                <div class="field">
                    <label for="phone">Phone Number</label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone') }}" required>
                </div>

                <div class="field">
                    <label for="loan_amount">Loan Amount</label>
                    <input id="loan_amount" name="loan_amount" type="number" min="1" step="0.01" value="{{ old('loan_amount') }}" required>
                </div>

                <div class="field">
                    <label for="monthly_income">Monthly Income</label>
                    <input id="monthly_income" name="monthly_income" type="number" min="1" step="0.01" value="{{ old('monthly_income') }}" required>
                </div>

                <div class="field field-full">
                    <label for="employment_type">Employment Type</label>
                    <select id="employment_type" name="employment_type" required>
                        <option value="">Select employment type</option>
                        <option value="salaried" @selected(old('employment_type') === 'salaried')>Salaried</option>
                        <option value="self_employed" @selected(old('employment_type') === 'self_employed')>Self Employed</option>
                    </select>
                </div>

                <div class="field field-full">
                    <label class="consent-box" for="consent">
                        <input id="consent" name="consent" type="checkbox" value="1" @checked(old('consent')) required>
                        <span>I confirm the submitted information is accurate and I consent to processing for loan review.</span>
                    </label>
                </div>

                <div class="field field-full">
                    <button type="submit" class="submit-btn">Submit Application</button>
                </div>
            </form>
        </article>
    </section>

    <section class="support-grid">
        <article class="info-card">
            <h3>What Happens Next</h3>
            <p>
                Applications are recorded instantly and risk level is calculated in real-time. High-risk applications are
                flagged for additional administrative review.
            </p>
        </article>
        <article id="faq" class="info-card">
            <h3>FAQ</h3>
            <p>
                Need help with eligibility, documents, or processing timelines? Submit your details first, then our team
                will follow up with the next required steps.
            </p>
        </article>
    </section>
@endsection
