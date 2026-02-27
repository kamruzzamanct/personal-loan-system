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
                        <option value="very_high" @selected($filters['risk_level'] === 'very_high')>Very High</option>
                        <option value="high" @selected($filters['risk_level'] === 'high')>High</option>
                        <option value="medium" @selected($filters['risk_level'] === 'medium')>Medium</option>
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
                    @can('export reports')
                        <a
                            href="{{ route('admin.loan-applications.export', array_merge(['format' => 'csv'], $filters)) }}"
                            class="btn-secondary"
                        >
                            Export CSV
                        </a>
                        <a
                            href="{{ route('admin.loan-applications.export', array_merge(['format' => 'xlsx'], $filters)) }}"
                            class="btn-secondary"
                        >
                            Export Excel
                        </a>
                    @endcan
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
                            <th>Assigned To</th>
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

                                $riskClass = match ($riskLevel) {
                                    'very_high' => 'risk-very-high',
                                    'high' => 'risk-high',
                                    'medium' => 'risk-medium',
                                    default => 'risk-low',
                                };

                                $status = $loanApplication->status instanceof \BackedEnum
                                    ? $loanApplication->status->value
                                    : (string) $loanApplication->status;

                                $statusClass = match ($status) {
                                    'approved' => 'status-approved',
                                    'under_review' => 'status-under-review',
                                    'declined' => 'status-declined',
                                    default => 'status-pending',
                                };
                            @endphp
                            <tr>
                                <td>{{ $loanApplication->first_name }} {{ $loanApplication->last_name }}</td>
                                <td>{{ $loanApplication->email }}</td>
                                <td>{{ $loanApplication->phone }}</td>
                                <td>{{ number_format((float) $loanApplication->loan_amount, 2) }}</td>
                                <td>{{ number_format((float) $loanApplication->monthly_income, 2) }}</td>
                                <td>{{ ucwords(str_replace('_', ' ', $employmentType)) }}</td>
                                <td>
                                    <span class="risk-pill {{ $riskClass }}">
                                        {{ strtoupper(str_replace('_', ' ', $riskLevel)) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="status-pill {{ $statusClass }}">
                                        {{ strtoupper(str_replace('_', ' ', $status)) }}
                                    </span>
                                </td>
                                <td>{{ $loanApplication->assignedToUser?->name ?? 'Unassigned' }}</td>
                                <td>{{ $loanApplication->created_at?->format('Y-m-d H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.loan-applications.show', $loanApplication) }}" class="table-action-link">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="admin-empty">No applications found for the selected filters.</td>
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
