# Personal Loan Application Management System

Production-ready Laravel 12 application for managing personal loan applications with risk scoring, admin review workflows, analytics, role-based access control, and exports.

## Stack
- Laravel 12
- PHP 8.2
- MySQL
- Laravel Queues + Mailables
- Spatie Laravel Permission
- Laravel Excel (CSV/XLSX export)

## Core Features
- Public loan application form with validation
- Automated repayment-risk detection (`low` / `medium` / `high` / `very_high`)
- High-risk notification email queue (`high` and `very_high`)
- Admin dashboard with operational analytics
- Admin applications module: list, filter, search, export, assign, status updates
- Manage users module with role assignment
- Applicant auth and applicant dashboard
- Role and permission enforcement via policies + middleware
- Risk-manager assignment workflow with scoped visibility (assigned applications only)

## Documentation
- [Project Guide](docs/PROJECT_GUIDE.md)
- [Local Setup Guide](docs/LOCAL_SETUP.md)
- [Server Deployment Guide](docs/DEPLOYMENT.md)

## Quick Start
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
npm install
npm run build
composer run dev
```

## Test Suite
```bash
php artisan test
```
