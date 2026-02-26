@extends('layouts.frontend')

@section('title', 'Personal Loan System')

@section('content')
    <section class="home-hero">
        <article class="home-intro">
            <p class="home-kicker">Personal Loan Platform</p>
            <h1 class="home-title">A safer and faster way to manage loan applications</h1>
            <p class="home-lead">
                This system helps applicants submit loan requests quickly while enabling admins to review risk, manage
                approvals, and monitor analytics from one structured workflow.
            </p>

            <div class="home-actions">
                <a href="{{ route('loan-applications.create') }}" class="btn-primary">Start Application</a>
                <a href="#overview" class="btn-secondary">Learn More</a>
            </div>
        </article>

        <aside class="home-side-card">
            <h2>Built for production teams</h2>
            <p>
                Validation-first submission, automated risk checks, high-risk notification flow, and an admin-ready data
                model designed for reporting and permission-based access.
            </p>

            <div class="metric-grid">
                <div class="metric-card">
                    <strong>Role Based</strong>
                    <span>Secure admin access and control</span>
                </div>
                <div class="metric-card">
                    <strong>Risk Aware</strong>
                    <span>Auto flagging for high-risk cases</span>
                </div>
                <div class="metric-card">
                    <strong>Queue Ready</strong>
                    <span>Asynchronous notification support</span>
                </div>
                <div class="metric-card">
                    <strong>Report Ready</strong>
                    <span>Dashboard and export workflows</span>
                </div>
            </div>
        </aside>
    </section>

    <section id="overview" class="overview-grid">
        <article id="benefits" class="overview-card">
            <h3>What the system does</h3>
            <p>
                Captures applicant data, validates required fields, and stores each application with normalized employment
                and risk metadata.
            </p>
        </article>

        <article class="overview-card">
            <h3>How risk is evaluated</h3>
            <p>
                Risk scoring is applied at submission time based on loan to income thresholds, with self-employed status
                explicitly tracked for reviewers.
            </p>
        </article>

        <article id="faq" class="overview-card">
            <h3>Typical flow</h3>
            <ol class="process-list">
                <li>Applicant submits the form.</li>
                <li>System validates and calculates risk.</li>
                <li>High-risk applications trigger admin alerts.</li>
                <li>Admin reviews data in dashboard views.</li>
            </ol>
        </article>
    </section>
@endsection
