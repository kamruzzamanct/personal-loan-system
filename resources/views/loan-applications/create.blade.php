@extends('layouts.frontend')

@section('title', 'Personal Loan Application')

@section('content')
    <section class="hero-grid loan-create-layout">
        <article class="hero-card">
            <p class="eyebrow">Fast & Secure</p>
            <h1>Personal Loan Application</h1>
            <p>
                Submit your application in minutes. Our system checks affordability and highlights risk indicators
                automatically to improve approval speed and review quality.
            </p>
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
                    <label for="first_name">First Name <span class="required-mark">*</span></label>
                    <input id="first_name" name="first_name" type="text" value="{{ old('first_name') }}" required>
                </div>

                <div class="field">
                    <label for="last_name">Last Name <span class="required-mark">*</span></label>
                    <input id="last_name" name="last_name" type="text" value="{{ old('last_name') }}" required>
                </div>

                <div class="field">
                    <label for="email">Email <span class="required-mark">*</span></label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required>
                </div>

                <div class="field">
                    <label for="age">Age <span class="required-mark">*</span></label>
                    <input id="age" name="age" type="number" min="20" step="1" value="{{ old('age') }}" required>
                </div>

                <div class="field">
                    <label for="phone">Phone Number <span class="required-mark">*</span></label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone') }}" required>
                </div>

                <div class="field field-full">
                    <label for="address">Address <span class="required-mark">*</span></label>
                    <input id="address" name="address" type="text" value="{{ old('address') }}" required>
                </div>

                <div class="field">
                    <label for="loan_amount">Loan Amount <span class="required-mark">*</span></label>
                    <div class="money-input">
                        <span class="money-prefix" aria-hidden="true">$</span>
                        <input id="loan_amount" name="loan_amount" type="number" min="1" step="0.01" value="{{ old('loan_amount') }}" required>
                    </div>
                </div>

                <div class="field">
                    <label for="monthly_income">Monthly Income <span class="required-mark">*</span></label>
                    <div class="money-input">
                        <span class="money-prefix" aria-hidden="true">$</span>
                        <input id="monthly_income" name="monthly_income" type="number" min="1" step="0.01" value="{{ old('monthly_income') }}" required>
                    </div>
                </div>

                <div class="field field-full">
                    <label for="employment_type">Employment Type <span class="required-mark">*</span></label>
                    <select id="employment_type" name="employment_type" required>
                        <option value="">Select employment type</option>
                        <option value="salaried" @selected(old('employment_type') === 'salaried')>Salaried</option>
                        <option value="self_employed" @selected(old('employment_type') === 'self_employed')>Self Employed</option>
                    </select>
                </div>

                <div class="field employment-conditional @if (old('employment_type') !== 'salaried') employment-hidden @endif" data-employment-salaried>
                    <label for="designation">Designation <span class="required-mark">*</span></label>
                    <input id="designation" name="designation" type="text" value="{{ old('designation') }}">
                </div>

                <div class="field employment-conditional @if (old('employment_type') !== 'salaried') employment-hidden @endif" data-employment-salaried>
                    <label for="company_name">Company Name <span class="required-mark">*</span></label>
                    <input id="company_name" name="company_name" type="text" value="{{ old('company_name') }}">
                </div>

                <div class="field field-full employment-conditional @if (old('employment_type') !== 'self_employed') employment-hidden @endif" data-employment-self-employed>
                    <label for="living_description">What You Do for Living <span class="required-mark">*</span></label>
                    <input id="living_description" name="living_description" type="text" value="{{ old('living_description') }}">
                </div>

                <div class="field field-full">
                    <label for="loan_proposal">Loan Proposal <span class="required-mark">*</span></label>
                    <textarea id="loan_proposal" name="loan_proposal" rows="4" required>{{ old('loan_proposal') }}</textarea>
                </div>

                <div class="field field-full">
                    <label class="consent-box" for="consent">
                        <input id="consent" name="consent" type="checkbox" value="1" @checked(old('consent')) required>
                        <span>I confirm the submitted information is accurate and I consent to processing for loan review. <span class="required-mark">*</span></span>
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

    <script>
        (() => {
            const employmentType = document.getElementById('employment_type');
            const salariedFields = document.querySelectorAll('[data-employment-salaried]');
            const selfEmployedFields = document.querySelectorAll('[data-employment-self-employed]');
            const designation = document.getElementById('designation');
            const companyName = document.getElementById('company_name');
            const livingDescription = document.getElementById('living_description');

            if (!employmentType) {
                return;
            }

            const setGroupVisibility = (fields, shouldShow) => {
                fields.forEach((field) => {
                    field.classList.toggle('employment-hidden', !shouldShow);

                    const inputs = field.querySelectorAll('input, select, textarea');
                    inputs.forEach((input) => {
                        input.disabled = !shouldShow;
                    });
                });
            };

            const toggleEmploymentFields = () => {
                const selected = employmentType.value;
                const isSalaried = selected === 'salaried';
                const isSelfEmployed = selected === 'self_employed';

                setGroupVisibility(salariedFields, isSalaried);
                setGroupVisibility(selfEmployedFields, isSelfEmployed);

                if (designation) {
                    designation.required = isSalaried;
                    if (!isSalaried) {
                        designation.value = '';
                    }
                }

                if (companyName) {
                    companyName.required = isSalaried;
                    if (!isSalaried) {
                        companyName.value = '';
                    }
                }

                if (livingDescription) {
                    livingDescription.required = isSelfEmployed;
                    if (!isSelfEmployed) {
                        livingDescription.value = '';
                    }
                }
            };

            employmentType.addEventListener('change', toggleEmploymentFields);
            toggleEmploymentFields();
        })();
    </script>
@endsection
