@extends('layouts.admin')

@section('title', 'Admin - Loan Applications')

@section('content')
    <section class="admin-page">
        <header class="admin-header">
            <h1>Loan Applications</h1>
            <p>Search, filter, and review all submitted applications. Default sort: latest submissions first.</p>
        </header>

        <article class="admin-card admin-filter-card">
            <form method="GET" action="{{ route('admin.loan-applications.index') }}" class="admin-filter-grid">
                <div class="field">
                    <label for="search">Search (Name or Email)</label>
                    <input
                        id="search"
                        name="search"
                        type="text"
                        placeholder="First name, last name, or email"
                        value="{{ $filters['search'] }}"
                    >
                </div>

                <div class="field">
                    <label for="risk_level">Risk Level</label>
                    <select id="risk_level" name="risk_level">
                        <option value="">All</option>
                        <option value="high" @selected($filters['risk_level'] === 'high')>High</option>
                        <option value="low" @selected($filters['risk_level'] === 'low')>Low</option>
                    </select>
                </div>

                <div class="field">
                    <label for="employment_type">Employment Type</label>
                    <select id="employment_type" name="employment_type">
                        <option value="">All</option>
                        <option value="salaried" @selected($filters['employment_type'] === 'salaried')>Salaried</option>
                        <option value="self_employed" @selected($filters['employment_type'] === 'self_employed')>Self Employed</option>
                    </select>
                </div>

                <div class="admin-filter-actions">
                    <button type="submit" class="btn-primary">Apply Filters</button>
                    <a href="{{ route('admin.loan-applications.index') }}" class="btn-secondary">Reset</a>
                </div>
            </form>
        </article>

        <article class="admin-card">
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Applicant</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Loan Amount</th>
                            <th>Monthly Income</th>
                            <th>Employment</th>
                            <th>Risk</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($loanApplications as $loanApplication)
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
                            <tr>
                                <td>{{ $loanApplication->first_name }} {{ $loanApplication->last_name }}</td>
                                <td>{{ $loanApplication->email }}</td>
                                <td>{{ $loanApplication->phone }}</td>
                                <td>{{ number_format((float) $loanApplication->loan_amount, 2) }}</td>
                                <td>{{ number_format((float) $loanApplication->monthly_income, 2) }}</td>
                                <td>{{ ucwords(str_replace('_', ' ', $employmentType)) }}</td>
                                <td>
                                    <span class="risk-pill {{ $riskLevel === 'high' ? 'risk-high' : 'risk-low' }}">
                                        {{ strtoupper($riskLevel) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="status-pill {{ $status === 'approved' ? 'status-approved' : 'status-pending' }}">
                                        {{ strtoupper($status) }}
                                    </span>
                                </td>
                                <td>{{ $loanApplication->created_at?->format('Y-m-d H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.loan-applications.show', $loanApplication) }}" class="table-action-link">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="admin-empty">No applications found for the selected filters.</td>
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
