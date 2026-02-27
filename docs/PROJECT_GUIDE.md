# Project Guide

## Overview
This system manages the end-to-end lifecycle of personal loan applications:
- Applicant submits a loan request from the public form
- System validates and evaluates repayment risk automatically
- High-risk submissions trigger queued admin notification emails
- Super Admin can assign applications to Risk Managers
- Risk Managers can only view and process applications assigned to them
- Admin team can review, filter, export, and update application status
- Applicant can track own applications from dashboard

## Tech Stack
- Laravel 12
- PHP 8.2
- MySQL
- Blade + Vite
- Laravel Mail + Queues
- `spatie/laravel-permission`
- `maatwebsite/excel`

## Implemented Modules
- Module 1: Loan application database/model/scopes
- Module 2: Form request validation
- Module 3: Risk service
- Module 4: Public controller create/store flow
- Module 5: Mailables + queued jobs
- Module 6: Admin applications listing, filtering, show, status updates
- Module 7: Roles/permissions (Spatie integration)
- Module 8: Dashboard analytics + charts
- Module 9: CSV/XLSX export with current filters
- Additional: Manage Users, Applicant auth/dashboard, assignment workflow

## Domain Model

### `users`
- Standard Laravel auth fields
- `role` enum: `super_admin`, `risk_manager`, `viewer`, `customer`
- Spatie roles are synced with role enum for admin users

### `loan_applications`
- Applicant identity:
  - `first_name`, `last_name`, `email`, `age`, `phone`, `address`
- Financial data:
  - `loan_amount`, `monthly_income`, `loan_proposal`
- Employment:
  - `employment_type`: `salaried`, `self_employed`
  - `designation`, `company_name` (salaried path)
  - `living_description` (self-employed path)
  - `is_self_employed`
- Risk:
  - `risk_level`: `low`, `medium`, `high`, `very_high`
- Workflow:
  - `status`: `pending`, `under_review`, `approved`, `declined`
  - `approved_at`, `approved_by_user_id`
  - `assigned_to_user_id`, `assigned_by_user_id`, `assigned_at`
- Applicant linkage:
  - `user_id` (optional link to authenticated applicant user)

## Core Business Rules

### Validation Rules (Highlights)
- Applicant must be at least 20 years old
- `designation` + `company_name` required if `employment_type = salaried`
- `living_description` required if `employment_type = self_employed`

### Risk Evaluation
`App\Services\LoanRiskService::calculateRisk()` uses repayment pressure (`loan_amount / monthly_income`):
- `very_high` when ratio `>= 3.5`
- `very_high` when `loan_amount > 50000` and `monthly_income < 25000`
- `high` when ratio `>= 2.0`
- `medium` when ratio `>= 1.75`
- otherwise `low`

`isHighRisk()` bucket includes: `high`, `very_high`.

### Notifications
- High-risk admin alert:
  - `SendHighRiskLoanNotificationJob`
  - Triggered when risk is `high` or `very_high`
- Loan approval customer email:
  - `SendLoanApprovedNotificationJob`
- Assignment email to risk manager:
  - `SendLoanAssignmentNotificationJob`

## Authentication and Access Model

### Guards
- `web`: applicant/public login flow
- `admin`: admin login flow

### Role Behavior
- Super Admin:
  - Full admin visibility
  - Can assign applications to risk managers
- Risk Manager:
  - Can only see assigned applications (index, show, dashboard, export)
  - Can update status only for assigned applications
- Viewer:
  - Read-only according to permissions

### Authorization
- Middleware:
  - `auth:admin`, `admin.role`, Spatie permission middleware
- Policy:
  - `LoanApplicationPolicy` controls `view`, `approve`, `filter`, `viewHighRisk`, `export`, `assign`

## Route Structure (High Level)
- Public:
  - `/`
  - `GET /loan-applications`
  - `POST /loan-applications`
- Applicant:
  - `/login`, `/register`
  - `/applicant/dashboard`
- Admin:
  - `/admin/login`
  - `/admin/dashboard`
  - `/admin/loan-applications`
  - `/admin/loan-applications/{id}`
  - `/admin/loan-applications/{id}/assign`
  - `/admin/loan-applications/{id}/under-review`
  - `/admin/loan-applications/{id}/approve`
  - `/admin/loan-applications/{id}/decline`
  - `/admin/loan-applications/export/{csv|xlsx}`
  - `/admin/users/*`

## Analytics
Dashboard aggregates:
- Total applications
- Approved loans
- High-risk applications (`high + very_high`)
- Salaried applications
- Self-employed applications
- High-risk percentage
- Monthly trend (last 12 months)
- Risk distribution chart:
  - `low`, `medium`, `high`, `very_high`

## Exports
`LoanApplicationsExport` exports current filtered query into:
- CSV
- XLSX

For Risk Managers, export data is scoped to assigned applications only.

## Key Directories
- `app/Enums`: shared enums for domain consistency
- `app/Http/Controllers/Admin`: admin features
- `app/Http/Controllers/Auth` and `app/Http/Controllers/Applicant`: applicant auth/dashboard
- `app/Jobs`, `app/Mail`: async email workflows
- `app/Policies`: permission checks
- `app/Services`: risk calculation logic
- `database/migrations`, `database/seeders`: schema + bootstrap data
- `resources/views/layouts`: frontend/admin master layouts
- `tests/Feature`, `tests/Unit`: controller/service/job/auth coverage

## Seeded Admin Bootstrap
`RolesAndPermissionsSeeder`:
- Creates permissions + roles
- Creates super admin from env values:
  - `SUPER_ADMIN_NAME`
  - `SUPER_ADMIN_EMAIL`
  - `SUPER_ADMIN_PASSWORD`
- Synchronizes user role enum and Spatie roles
