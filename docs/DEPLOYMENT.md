# Server Deployment Guide

## Recommended Production Stack
- Linux server (Ubuntu 22.04+)
- Nginx
- PHP 8.2 + PHP-FPM
- MySQL 8+
- Supervisor (queue workers)
- Node.js (build pipeline or CI artifact build)

## 1. Prepare Server Packages
Install required packages (example):
```bash
sudo apt update
sudo apt install -y nginx mysql-server php8.2-fpm php8.2-cli php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip unzip git supervisor
```

## 2. Deploy Application Code
```bash
cd /var/www
git clone <your-repository-url> personal-loan-system
cd personal-loan-system
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

If assets are built in CI/CD, skip `npm install` and `npm run build` on server and deploy compiled assets directly.

## 3. Configure `.env` for Production
```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=personal_loan_system
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-smtp-username
MAIL_PASSWORD=your-smtp-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@your-domain.com
MAIL_FROM_NAME="Personal Loan System"

SUPER_ADMIN_NAME="Super Admin"
SUPER_ADMIN_EMAIL=superadmin@your-domain.com
SUPER_ADMIN_PASSWORD=change-this-strong-password
```

Then run:
```bash
php artisan key:generate --force
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan optimize
```

## 4. Configure File Permissions
```bash
sudo chown -R www-data:www-data /var/www/personal-loan-system
sudo chmod -R 775 /var/www/personal-loan-system/storage /var/www/personal-loan-system/bootstrap/cache
```

## 5. Nginx Virtual Host
Example `/etc/nginx/sites-available/personal-loan-system`:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/personal-loan-system/public;

    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/personal-loan-system /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

## 6. Queue Worker (Supervisor)
Create `/etc/supervisor/conf.d/personal-loan-queue.conf`:

```ini
[program:personal-loan-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/personal-loan-system/artisan queue:work database --queue=mail,default --sleep=3 --tries=3 --timeout=120
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/personal-loan-system/storage/logs/queue-worker.log
stopwaitsecs=3600
```

Apply:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl status
```

## 7. Post-Deployment Verification
1. Open `/admin/login` and sign in as super admin.
2. Create a Risk Manager user in admin user management.
3. Submit a high-risk application (`high` or `very_high`).
4. Assign the application to the Risk Manager.
5. Verify assignment email is queued/sent.
6. Login as Risk Manager and verify:
   - only assigned applications are visible in list/dashboard/export
   - status updates work on assigned applications
7. Approve a loan and verify applicant approval email is queued/sent.

## 8. Operations Commands
```bash
php artisan queue:failed
php artisan queue:retry all
php artisan queue:flush
php artisan optimize:clear
php artisan optimize
```

After code update:
```bash
git pull
composer install --no-dev --optimize-autoloader
npm run build
php artisan migrate --force
php artisan optimize
sudo supervisorctl restart personal-loan-queue:*
```

## 9. Security and Reliability Notes
- Use HTTPS (LetsEncrypt or managed TLS)
- Keep `APP_DEBUG=false` in production
- Rotate `SUPER_ADMIN_PASSWORD` immediately after first deployment
- Restrict server firewall to required ports only
- Back up MySQL regularly and test restores
