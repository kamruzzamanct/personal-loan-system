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

        <section class="admin-chart-grid">
            <article class="admin-card admin-chart-card">
                <h2 class="admin-section-title">Applications Per Month (Last 12 Months)</h2>
                <p class="admin-muted">Monthly submission trend for reporting and operational planning.</p>
                <div class="admin-chart-wrap">
                    <canvas id="applicationsPerMonthChart" aria-label="Applications per month chart" role="img"></canvas>
                </div>
            </article>

            <article class="admin-card admin-chart-card">
                <h2 class="admin-section-title">Risk Distribution</h2>
                <p class="admin-muted">Current split of high-risk and low-risk applications.</p>
                <div class="admin-chart-wrap">
                    <canvas id="riskDistributionChart" aria-label="Risk distribution chart" role="img"></canvas>
                </div>
            </article>
        </section>

        <article class="admin-card">
            <div class="home-actions">
                <a href="{{ route('admin.loan-applications.index') }}" class="btn-primary">Review Applications</a>
            </div>
        </article>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof window.Chart === 'undefined') {
                return;
            }

            const monthlyChartData = @json($monthlyApplicationsChart);
            const riskChartData = @json($riskDistributionChart);

            const monthlyCanvas = document.getElementById('applicationsPerMonthChart');
            if (monthlyCanvas) {
                new window.Chart(monthlyCanvas, {
                    type: 'line',
                    data: {
                        labels: monthlyChartData.labels,
                        datasets: [{
                            label: 'Applications',
                            data: monthlyChartData.series,
                            borderColor: '#016aac',
                            backgroundColor: 'rgba(1, 106, 172, 0.14)',
                            borderWidth: 2,
                            pointRadius: 3,
                            pointBackgroundColor: '#245a7c',
                            pointHoverRadius: 4,
                            tension: 0.25,
                            fill: true,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                            },
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0,
                                },
                            },
                        },
                    },
                });
            }

            const riskCanvas = document.getElementById('riskDistributionChart');
            if (riskCanvas) {
                new window.Chart(riskCanvas, {
                    type: 'pie',
                    data: {
                        labels: riskChartData.labels,
                        datasets: [{
                            data: riskChartData.series,
                            backgroundColor: ['#245a7c', '#a8c930'],
                            borderColor: '#ffffff',
                            borderWidth: 2,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                            },
                        },
                    },
                });
            }
        });
    </script>
@endsection
