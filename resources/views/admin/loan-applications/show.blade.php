@extends('layouts.admin')

@section('title', 'Admin - Loan Application Details')

@section('content')
    @php
        $employmentType = $loanApplication->employment_type instanceof \BackedEnum
            ? $loanApplication->employment_type->value
            : (string) $loanApplication->employment_type;

        $riskLevel = $loanApplication->risk_level instanceof \BackedEnum
            ? $loanApplication->risk_level->value
            : (string) $loanApplication->risk_level;

        $status = $loanApplication->status instanceof \BackedEnum
            ? $loanApplication->status->value
            : (string) $loanApplication->status;
    @endphp

    <section class="admin-page">
        <header class="admin-header admin-header-inline">
            <div>
                <h1>Loan Application Details</h1>
                <p>Review the applicant information and approve the loan when ready.</p>
            </div>
            <a href="{{ route('admin.loan-applications.index') }}" class="btn-secondary">Back to List</a>
        </header>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="admin-detail-grid">
            <article class="admin-card">
                <h2 class="admin-section-title">Applicant Information</h2>
                <div class="admin-detail-table-wrap">
                    <table class="admin-detail-table">
                        <tr>
                            <th>Full Name</th>
                            <td>{{ $loanApplication->first_name }} {{ $loanApplication->last_name }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $loanApplication->email }}</td>
                        </tr>
                        <tr>
                            <th>Phone</th>
                            <td>{{ $loanApplication->phone }}</td>
                        </tr>
                        <tr>
                            <th>Loan Amount</th>
                            <td>{{ number_format((float) $loanApplication->loan_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Monthly Income</th>
                            <td>{{ number_format((float) $loanApplication->monthly_income, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Employment Type</th>
                            <td>{{ ucwords(str_replace('_', ' ', $employmentType)) }}</td>
                        </tr>
                        <tr>
                            <th>Self Employed Flag</th>
                            <td>{{ $loanApplication->is_self_employed ? 'Yes' : 'No' }}</td>
                        </tr>
                        <tr>
                            <th>Submitted At</th>
                            <td>{{ $loanApplication->created_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </article>

            <article class="admin-card">
                <h2 class="admin-section-title">Review & Approval</h2>

                <div class="admin-detail-badges">
                    <span class="risk-pill {{ $riskLevel === 'high' ? 'risk-high' : 'risk-low' }}">
                        Risk: {{ strtoupper($riskLevel) }}
                    </span>
                    <span class="status-pill {{ $status === 'approved' ? 'status-approved' : 'status-pending' }}">
                        Status: {{ strtoupper($status) }}
                    </span>
                </div>

                <ul class="admin-meta-list">
                    <li>
                        <span>Approved At</span>
                        <strong>{{ $loanApplication->approved_at?->format('Y-m-d H:i') ?? 'Not approved yet' }}</strong>
                    </li>
                    <li>
                        <span>Approved By</span>
                        <strong>{{ $loanApplication->approvedByUser?->name ?? 'Not assigned' }}</strong>
                    </li>
                </ul>

                @if ($riskLevel === 'high')
                    <p class="admin-note-high-risk">
                        This application is flagged as high risk and should be reviewed carefully before approval.
                    </p>
                @endif

                @if ($status !== 'approved')
                    @can('approve applications')
                        <form action="{{ route('admin.loan-applications.approve', $loanApplication) }}" method="POST" class="admin-approve-form">
                            @csrf
                            <button type="submit" class="btn-primary">Approve Loan</button>
                        </form>
                    @else
                        <p class="admin-muted">You do not have permission to approve applications.</p>
                    @endcan
                @else
                    <p class="admin-muted">This application is already approved and the customer notification has been sent.</p>
                @endif
            </article>
        </div>
    </section>
@endsection
