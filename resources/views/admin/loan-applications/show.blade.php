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

        $adminUser = auth('admin')->user();
        $isSuperAdmin = $adminUser && method_exists($adminUser, 'isSuperAdmin') && $adminUser->isSuperAdmin();
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
                            <th>Age</th>
                            <td>{{ $loanApplication->age ?? 'Not provided' }}</td>
                        </tr>
                        <tr>
                            <th>Phone</th>
                            <td>{{ $loanApplication->phone }}</td>
                        </tr>
                        <tr>
                            <th>Address</th>
                            <td>{{ $loanApplication->address ?: 'Not provided' }}</td>
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
                        @if ($employmentType === 'salaried')
                            <tr>
                                <th>Designation</th>
                                <td>{{ $loanApplication->designation ?: 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <th>Company Name</th>
                                <td>{{ $loanApplication->company_name ?: 'Not provided' }}</td>
                            </tr>
                        @endif
                        @if ($employmentType === 'self_employed')
                            <tr>
                                <th>What You Do for Living</th>
                                <td>{{ $loanApplication->living_description ?: 'Not provided' }}</td>
                            </tr>
                        @endif
                        <tr>
                            <th>Loan Proposal</th>
                            <td class="admin-preline">{{ $loanApplication->loan_proposal ?: 'Not provided' }}</td>
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
                    <span class="risk-pill {{ $riskClass }}">
                        Risk: {{ strtoupper(str_replace('_', ' ', $riskLevel)) }}
                    </span>
                    <span class="status-pill {{ $statusClass }}">
                        Status: {{ strtoupper(str_replace('_', ' ', $status)) }}
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
                    <li>
                        <span>Assigned To</span>
                        <strong>{{ $loanApplication->assignedToUser?->name ?? 'Unassigned' }}</strong>
                    </li>
                    <li>
                        <span>Assigned At</span>
                        <strong>{{ $loanApplication->assigned_at?->format('Y-m-d H:i') ?? 'Not assigned yet' }}</strong>
                    </li>
                    <li>
                        <span>Assigned By</span>
                        <strong>{{ $loanApplication->assignedByUser?->name ?? 'Not assigned' }}</strong>
                    </li>
                </ul>

                @if (in_array($riskLevel, ['high', 'very_high'], true))
                    <p class="admin-note-high-risk">
                        This application is flagged as high risk and should be reviewed carefully before approval.
                    </p>
                @endif

                @if ($errors->has('status'))
                    <div class="alert alert-error">{{ $errors->first('status') }}</div>
                @endif

                @if ($errors->has('risk_manager_user_id'))
                    <div class="alert alert-error">{{ $errors->first('risk_manager_user_id') }}</div>
                @endif

                @if ($isSuperAdmin)
                    <form action="{{ route('admin.loan-applications.assign', $loanApplication) }}" method="POST" class="admin-assign-form">
                        @csrf
                        <div class="field">
                            <label for="risk_manager_user_id">Assign to Risk Manager</label>
                            <select id="risk_manager_user_id" name="risk_manager_user_id" required>
                                <option value="">Select risk manager</option>
                                @foreach ($riskManagers as $riskManager)
                                    <option value="{{ $riskManager->id }}" @selected((string) old('risk_manager_user_id', (string) $loanApplication->assigned_to_user_id) === (string) $riskManager->id)>
                                        {{ $riskManager->name }} ({{ $riskManager->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn-secondary">Assign Application</button>
                    </form>
                @endif

                @can('approve applications')
                    @if ($status !== 'approved')
                        <div class="admin-status-actions">
                            @if ($status !== 'under_review')
                                <form action="{{ route('admin.loan-applications.under-review', $loanApplication) }}" method="POST" class="admin-inline-form">
                                    @csrf
                                    <button type="submit" class="btn-secondary">Mark Under Review</button>
                                </form>
                            @endif

                            @if ($status !== 'declined')
                                <form action="{{ route('admin.loan-applications.decline', $loanApplication) }}" method="POST" class="admin-inline-form">
                                    @csrf
                                    <button type="submit" class="btn-danger-outline">Decline</button>
                                </form>
                            @endif

                            <form action="{{ route('admin.loan-applications.approve', $loanApplication) }}" method="POST" class="admin-inline-form">
                                @csrf
                                <button type="submit" class="btn-primary">Approve Loan</button>
                            </form>
                        </div>
                    @else
                        <p class="admin-muted">This application is already approved and the customer notification has been sent.</p>
                    @endif
                @else
                    <p class="admin-muted">You do not have permission to update application status.</p>
                @endcan
            </article>
        </div>
    </section>
@endsection
