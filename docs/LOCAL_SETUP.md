# Local Setup Guide

## Prerequisites
- PHP 8.2+
- Composer 2+
- Node.js 18+ and npm
- MySQL 8+ (or compatible)
- Git

## 1. Install Dependencies
```bash
composer install
npm install
```

## 2. Configure Environment
```bash
cp .env.example .env
php artisan key:generate
```

Update `.env` values:
```dotenv
APP_NAME="Personal Loan System"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=personal_loan_system
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database
MAIL_MAILER=log

SUPER_ADMIN_NAME="Super Admin"
SUPER_ADMIN_EMAIL=superadmin@company.com
SUPER_ADMIN_PASSWORD=password12345
```

## 3. Database Migration and Seed
Create your local MySQL database, then run:
```bash
php artisan migrate
php artisan db:seed
```

## 4. Build Frontend Assets
```bash
npm run build
```

For active frontend development:
```bash
npm run dev
```

## 5. Run the App

### Option A (recommended)
Runs web server, queue listener, logs, and Vite together:
```bash
composer run dev
```

### Option B (separate terminals)
```bash
php artisan serve
php artisan queue:work --queue=mail,default
npm run dev
```

## 6. Access the Application
- Home: `http://127.0.0.1:8000/`
- Loan form: `http://127.0.0.1:8000/loan-applications`
- Applicant login: `http://127.0.0.1:8000/login`
- Admin login: `http://127.0.0.1:8000/admin/login`

Seeded admin credentials:
- Email: `SUPER_ADMIN_EMAIL` from `.env`
- Password: `SUPER_ADMIN_PASSWORD` from `.env`

## 7. Mail and Queue in Local

Current queued mail jobs (`mail` queue):
- High-risk admin alert (`high` / `very_high`)
- Loan approved customer notification
- Loan assignment notification to risk manager

If `MAIL_MAILER=log`, emails are written to:
- `storage/logs/laravel.log`

To process queued mail jobs:
```bash
php artisan queue:work --queue=mail,default
```

## 8. Quick Functional Smoke Test
1. Login as super admin.
2. Create a Risk Manager user from admin user management.
3. Submit a loan application from public form.
4. Open admin application details and assign it to Risk Manager.
5. Login as Risk Manager and verify:
   - only assigned application appears in list/dashboard
   - status update works for assigned application
6. Approve the application and verify approval email queue job.

## 9. Run Tests
```bash
php artisan test
```

## Troubleshooting

### `PermissionDoesNotExist: approve applications`
Usually caused by stale permission cache or partially-seeded DB.

Run:
```bash
php artisan optimize:clear
php artisan migrate:fresh --seed
```

### Admin login does not redirect correctly
Clear cached config/routes:
```bash
php artisan optimize:clear
```

### Queue jobs are not running
Check:
- `QUEUE_CONNECTION=database`
- `jobs` table exists (run migrations)
- Queue worker is running

Inspect failed jobs:
```bash
php artisan queue:failed
php artisan queue:retry all
```
