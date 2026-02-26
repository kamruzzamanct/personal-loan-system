@extends('layouts.frontend')

@section('title', 'Applicant Dashboard')

@section('content')
    <section class="admin-page">
        <header class="admin-header">
            <h1>My Loan Applications</h1>
            <p>Welcome, {{ $user->name }}. Here are your submitted loan applications and current statuses.</p>
        </header>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <article class="admin-card">
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Loan Amount</th>
                            <th>Monthly Income</th>
                            <th>Employment</th>
                            <th>Status</th>
                            <th>Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($loanApplications as $loanApplication)
                            @php
                                $employmentType = $loanApplication->employment_type instanceof \BackedEnum
                                    ? $loanApplication->employment_type->value
                                    : (string) $loanApplication->employment_type;

                                $status = $loanApplication->status instanceof \BackedEnum
                                    ? $loanApplication->status->value
                                    : (string) $loanApplication->status;
                            @endphp
                            <tr>
                                <td>{{ (($loanApplications->currentPage() - 1) * $loanApplications->perPage()) + $loop->iteration }}</td>
                                <td>{{ number_format((float) $loanApplication->loan_amount, 2) }}</td>
                                <td>{{ number_format((float) $loanApplication->monthly_income, 2) }}</td>
                                <td>{{ ucwords(str_replace('_', ' ', $employmentType)) }}</td>
                                <td>
                                    <span class="status-pill {{ $status === 'approved' ? 'status-approved' : 'status-pending' }}">
                                        {{ strtoupper($status) }}
                                    </span>
                                </td>
                                <td>{{ $loanApplication->created_at?->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="admin-empty">No loan applications found for your account yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="admin-pagination">
                {{ $loanApplications->onEachSide(1)->links() }}
            </div>
        </article>
    </section>
@endsection
