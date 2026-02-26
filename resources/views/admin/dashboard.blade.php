@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
    <section class="admin-page">
        <header class="admin-header">
            <h1>Admin Dashboard</h1>
            <p>Welcome back, {{ $user?->name ?? 'Admin' }}. Here is your operational snapshot.</p>
        </header>

        <section class="admin-stat-grid">
            <article class="admin-stat-card">
                <p>Total Applications</p>
                <strong>{{ number_format($totalApplications) }}</strong>
            </article>
            <article class="admin-stat-card">
                <p>Approved Loans</p>
                <strong>{{ number_format($approvedLoans) }}</strong>
            </article>
            <article class="admin-stat-card">
                <p>High Risk Applications</p>
                <strong>{{ number_format($highRiskApplications) }}</strong>
            </article>
            <article class="admin-stat-card">
                <p>Salaried Applicants</p>
                <strong>{{ number_format($salariedApplications) }}</strong>
            </article>
            <article class="admin-stat-card">
                <p>Self Employed Applicants</p>
                <strong>{{ number_format($selfEmployedApplications) }}</strong>
            </article>
            <article class="admin-stat-card">
                <p>High Risk Ratio</p>
                <strong>{{ number_format($highRiskPercentage, 2) }}%</strong>
            </article>
        </section>

        <section class="admin-dashboard-grid">
            <article class="admin-card">
                <h2 class="admin-section-title">Admin Information</h2>
                @php
                    $roleRaw = $user?->role;
                    $roleValue = $roleRaw instanceof \BackedEnum ? $roleRaw->value : (string) $roleRaw;
                @endphp
                <ul class="admin-meta-list">
                    <li><span>Name</span><strong>{{ $user?->name ?? 'N/A' }}</strong></li>
                    <li><span>Email</span><strong>{{ $user?->email ?? 'N/A' }}</strong></li>
                    <li><span>Role</span><strong>{{ $roleValue !== '' ? ucwords(str_replace('_', ' ', $roleValue)) : 'N/A' }}</strong></li>
                    <li><span>Environment</span><strong>{{ strtoupper(app()->environment()) }}</strong></li>
                    <li><span>Timezone</span><strong>{{ config('app.timezone') }}</strong></li>
                </ul>
            </article>

            <article class="admin-card">
                <h2 class="admin-section-title">Recent High Risk Cases</h2>

                @if ($latestHighRiskApplications->isEmpty())
                    <p class="admin-muted">No high-risk applications available yet.</p>
                @else
                    <ul class="admin-highrisk-list">
                        @foreach ($latestHighRiskApplications as $application)
                            <li>
                                <div>
                                    <strong>{{ $application->first_name }} {{ $application->last_name }}</strong>
                                    <small>{{ $application->email }}</small>
                                </div>
                                <span class="risk-pill risk-high">HIGH</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </article>
        </section>

        <article class="admin-card">
            <div class="home-actions">
                <a href="{{ route('admin.loan-applications.index') }}" class="btn-primary">Review Applications</a>
            </div>
        </article>
    </section>
@endsection
