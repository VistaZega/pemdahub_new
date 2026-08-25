# DOKUMENTASI TEKNIS 05: DEPLOYMENT GUIDE

**Sistem Manajemen Sekolah Yayasan Perguruan Pembangunan Daerah Nias (PEMBDA)**  
**Versi:** 3.0.0  
**Tanggal:** 25 Agustus 2026

---

## DAFTAR ISI

1. [System Requirements](#1-system-requirements)
2. [Development Environment Setup](#2-development-environment-setup)
3. [Production Deployment](#3-production-deployment)
4. [Database Migration](#4-database-migration)
5. [Configuration](#5-configuration)
6. [Security](#6-security)
7. [Performance Optimization](#7-performance-optimization)
8. [Backup & Recovery](#8-backup--recovery)
9. [Monitoring](#9-monitoring)
10. [Troubleshooting](#10-troubleshooting)

---

## 1. SYSTEM REQUIREMENTS

### Minimum Requirements

**Server:**

- **OS:** Linux (Ubuntu 20.04+), Windows Server 2019+, or macOS
- **CPU:** 2 cores / 2.0 GHz
- **RAM:** 4 GB
- **Storage:** 20 GB SSD
- **Network:** 10 Mbps

**Software:**

- **Web Server:** Apache 2.4+ or Nginx 1.18+
- **PHP:** 8.2.0 or higher
- **Database:** MySQL 8.0+ or MariaDB 10.6+
- **Composer:** 2.6+
- **Node.js:** 18+ (for asset compilation, optional)

### Recommended Requirements

**Server:**

- **OS:** Ubuntu 22.04 LTS
- **CPU:** 4 cores / 2.4 GHz
- **RAM:** 8 GB
- **Storage:** 50 GB SSD
- **Network:** 100 Mbps

**Software:**

- **Web Server:** Nginx 1.24+
- **PHP:** 8.2.12
- **Database:** MySQL 8.0.35
- **Composer:** 2.7+
- **Redis:** 7.0+ (for caching and sessions)

### PHP Extensions Required

```
- OpenSSL
- PDO
- Mbstring
- Tokenizer
- XML
- Ctype
- JSON
- BCMath
- Fileinfo
- GD or Imagick (for image processing)
- Zip
- MySQL/MariaDB driver
```

### Check PHP Configuration

```bash
php -v
php -m | grep -i "pdo\|mbstring\|openssl\|tokenizer\|xml"
```

---

## 2. DEVELOPMENT ENVIRONMENT SETUP

### 2.1 Windows (XAMPP)

**Step 1: Install XAMPP**

1. Download XAMPP from https://www.apachefriends.org/
2. Install to `C:\xampp`
3. Start Apache and MySQL from XAMPP Control Panel

**Step 2: Clone Repository**

```bash
cd C:\xampp\htdocs
git clone <repository-url> pembdahub
cd pembdahub
```

**Step 3: Install Dependencies**

```bash
# Install Composer dependencies
composer install

# Copy environment file
copy .env.example .env

# Generate application key
php artisan key:generate
```

**Step 4: Configure Database**

Edit `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pembda_hub
DB_USERNAME=root
DB_PASSWORD=
```

**Step 5: Create Database**

```sql
-- Open phpMyAdmin or MySQL console
CREATE DATABASE pembda_hub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

**Step 6: Run Migrations**

```bash
php artisan migrate
```

**Step 7: Seed Database (Optional)**

```bash
php artisan db:seed
```

**Step 8: Create Storage Link**

```bash
php artisan storage:link
```

**Step 9: Set Permissions**

```bash
# Windows (run as Administrator)
icacls "C:\xampp\htdocs\pembdahub\storage" /grant Users:(OI)(CI)F /T
icacls "C:\xampp\htdocs\pembdahub\bootstrap\cache" /grant Users:(OI)(CI)F /T
```

**Step 10: Access Application**

```
http://localhost/pembdahub/public
```

---

### 2.2 Linux (Ubuntu)

**Step 1: Update System**

```bash
sudo apt update && sudo apt upgrade -y
```

**Step 2: Install LAMP Stack**

```bash
# Install Apache
sudo apt install apache2 -y

# Install MySQL
sudo apt install mysql-server -y

# Install PHP 8.2 and extensions
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.2 php8.2-cli php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath -y
```

**Step 3: Install Composer**

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

**Step 4: Clone Repository**

```bash
cd /var/www/html
sudo git clone <repository-url> pembdahub
cd pembdahub
sudo chown -R www-data:www-data .
```

**Step 5: Install Dependencies**

```bash
composer install --no-dev --optimize-autoloader
```

**Step 6: Configure Environment**

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=http://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pembda_hub
DB_USERNAME=pembda_user
DB_PASSWORD=secure_password_here
```

**Step 7: Configure Database**

```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE pembda_hub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'pembda_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON pembda_hub.* TO 'pembda_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

**Step 8: Run Migrations**

```bash
php artisan migrate --force
```

**Step 9: Set Permissions**

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

**Step 10: Configure Apache**

```bash
sudo nano /etc/apache2/sites-available/pembdahub.conf
```

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    ServerAdmin admin@your-domain.com
    DocumentRoot /var/www/html/pembdahub/public

    <Directory /var/www/html/pembdahub/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/pembdahub-error.log
    CustomLog ${APACHE_LOG_DIR}/pembdahub-access.log combined
</VirtualHost>
```

**Enable site and rewrite module:**

```bash
sudo a2ensite pembdahub.conf
sudo a2enmod rewrite
sudo systemctl restart apache2
```

---

## 3. PRODUCTION DEPLOYMENT

### 3.1 Pre-Deployment Checklist

- [ ] Backup current database
- [ ] Test on staging environment
- [ ] Update `.env` with production settings
- [ ] Set `APP_DEBUG=false`
- [ ] Set `APP_ENV=production`
- [ ] Configure proper `APP_URL`
- [ ] Set strong `APP_KEY`
- [ ] Configure database credentials
- [ ] Configure mail settings
- [ ] Configure WhatsApp API (Fonnte)
- [ ] Set up SSL certificate
- [ ] Configure firewall rules
- [ ] Set up monitoring tools

### 3.2 Deployment Steps

**Step 1: Pull Latest Code**

```bash
cd /var/www/html/pembdahub
git pull origin main
```

**Step 2: Install/Update Dependencies**

```bash
composer install --no-dev --optimize-autoloader
```

**Step 3: Clear Caches**

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

**Step 4: Cache Configuration**

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Step 5: Run Migrations**

```bash
php artisan migrate --force
```

**Step 6: Optimize Autoloader**

```bash
composer dump-autoload --optimize
```

**Step 7: Set Permissions**

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

**Step 8: Restart Services**

```bash
sudo systemctl restart apache2
# or for Nginx
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
```

**Step 9: Verify Deployment**

- Check application accessible
- Test login functionality
- Test PSB registration
- Check database connectivity
- Verify file uploads work
- Test WhatsApp notifications

---

### 3.3 SSL Certificate Setup (Let's Encrypt)

**Install Certbot:**

```bash
sudo apt install certbot python3-certbot-apache -y
```

**Obtain Certificate:**

```bash
sudo certbot --apache -d your-domain.com -d www.your-domain.com
```

**Auto-renewal (already configured by Certbot):**

```bash
sudo certbot renew --dry-run
```

**Update .env:**

```env
APP_URL=https://your-domain.com
SESSION_SECURE_COOKIE=true
```

---

## 4. DATABASE MIGRATION

### 4.1 Fresh Installation

```bash
# Run all migrations
php artisan migrate

# Seed database with initial data
php artisan db:seed
```

### 4.2 Update Existing Database

```bash
# Check migration status
php artisan migrate:status

# Run pending migrations
php artisan migrate

# Rollback last batch (if needed)
php artisan migrate:rollback

# Rollback all and re-run (DANGER: data loss)
php artisan migrate:fresh
```

### 4.3 Import from Backup

```bash
# Import SQL backup
mysql -u pembda_user -p pembda_hub < backup.sql

# or using MySQL Workbench / phpMyAdmin
```

---

## 5. CONFIGURATION

### 5.1 Environment Variables

**File:** `.env`

```env
# Application
APP_NAME="Pembda Hub"
APP_ENV=production
APP_KEY=base64:generated_key_here
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pembda_hub
DB_USERNAME=pembda_user
DB_PASSWORD=secure_password

# Cache & Session
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=database

# Redis (Optional - for better performance)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"

# WhatsApp (Fonnte)
WHATSAPP_ENABLED=true
WHATSAPP_PROVIDER=fonnte
WHATSAPP_API_TOKEN=your_fonnte_token_here
WHATSAPP_SENDER=your_whatsapp_number

# Locale
APP_LOCALE=id
APP_FAKER_LOCALE=id_ID

# File Storage
FILESYSTEM_DISK=public

# Logging
LOG_CHANNEL=daily
LOG_LEVEL=error
```

### 5.2 Web Server Configuration

**Apache (.htaccess in public/):**

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

**Nginx:**

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com;
    root /var/www/html/pembdahub/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Increase upload size
    client_max_body_size 20M;
}
```

---

## 6. QUEUE WORKER & SCHEDULED TASKS

### 6.1 Queue Worker

Pembda Hub uses **database queue driver** for background jobs:

- `SendWhatsAppMessage` — Individual WhatsApp notifications
- `SendWhatsAppTemplate` — Templated WhatsApp messages
- `GenerateBulkReportCards` — Batch PDF report card generation

**Start Queue Worker (Development):**

```bash
php artisan queue:work --sleep=3 --tries=3 --max-time=3600
```

**Production Setup with Supervisor:**

Install Supervisor:

```bash
sudo apt install supervisor -y
```

Create config file `/etc/supervisor/conf.d/pembdahub-worker.conf`:

```ini
[program:pembdahub-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/pembdahub/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/html/pembdahub/storage/logs/worker.log
stopwaitsecs=3600
```

Start Supervisor:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start pembdahub-worker:*
```

Monitor Workers:

```bash
sudo supervisorctl status pembdahub-worker:*
```

**After Deployment — Restart Workers:**

```bash
php artisan queue:restart
```

### 6.2 Scheduled Tasks (Cron)

The application has 6 scheduled tasks defined in `routes/console.php`:

| Schedule     | Task                     | Description                         |
| ------------ | ------------------------ | ----------------------------------- |
| Daily 02:00  | `queue:prune-failed`     | Prune failed jobs older than 7 days |
| Daily 02:30  | `queue:prune-batches`    | Prune completed job batches (48h)   |
| Daily 03:00  | `queue:restart`          | Restart workers to free memory      |
| Daily 04:00  | `auth:clear-resets`      | Clear expired password reset tokens |
| Hourly       | `cache:prune-stale-tags` | Prune stale cache tags              |
| Every 15 min | Health check closure     | DB, storage, queue backlog check    |

**Setup Cron (Linux):**

```bash
sudo crontab -e -u www-data
```

Add line:

```cron
* * * * * cd /var/www/html/pembdahub && php artisan schedule:run >> /dev/null 2>&1
```

**Windows Task Scheduler (XAMPP):**

Create a batch file `C:\xampp\htdocs\pembdahub\scheduler.bat`:

```bat
cd C:\xampp\htdocs\pembdahub
C:\xampp\php\php.exe artisan schedule:run
```

Add to Windows Task Scheduler: run `scheduler.bat` every minute.

### 6.3 Monitoring Queue Health

```bash
# View pending jobs
php artisan queue:monitor default

# View failed jobs
php artisan queue:failed

# Retry a failed job
php artisan queue:retry <job-id>

# Retry all failed jobs
php artisan queue:retry all

# Clear all failed jobs
php artisan queue:flush
```

---

## 7. SECURITY

### 7.1 Security Checklist

- [x] Set `APP_DEBUG=false` in production
- [ ] Use HTTPS (SSL certificate)
- [ ] Strong database password
- [x] Disable directory listing
- [x] Hide Laravel version
- [ ] Configure CORS properly
- [x] Use secure session cookies (HttpOnly, Secure flags)
- [x] Implement rate limiting (ThrottleRequests middleware)
- [ ] Regular security updates
- [x] File upload validation
- [x] SQL injection protection (Eloquent ORM)
- [x] XSS protection (Blade escaping)
- [x] CSRF protection (VerifyCsrfToken middleware)
- [x] Security headers (X-Content-Type-Options, X-Frame-Options, X-XSS-Protection)
- [x] Password policy enforcement (min 8 chars, mixed case, numbers, symbols)
- [x] Form Request validation (13 dedicated Form Request classes)

### 7.2 File Permissions

```bash
# Production permissions
sudo chown -R www-data:www-data /var/www/html/pembdahub
sudo find /var/www/html/pembdahub -type f -exec chmod 644 {} \;
sudo find /var/www/html/pembdahub -type d -exec chmod 755 {} \;
sudo chmod -R 775 /var/www/html/pembdahub/storage
sudo chmod -R 775 /var/www/html/pembdahub/bootstrap/cache
```

### 7.3 Firewall Configuration

```bash
# UFW (Ubuntu)
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
sudo ufw status
```

### 7.4 Secure .env File

```bash
# Prevent .env from being accessed via web
# Already configured in .htaccess and nginx config
# Double check:
sudo chmod 600 .env
```

---

## 8. PERFORMANCE OPTIMIZATION

### 8.1 Caching

**Config Cache:**

```bash
php artisan config:cache
```

**Route Cache:**

```bash
php artisan route:cache
```

**View Cache:**

```bash
php artisan view:cache
```

**Clear All Caches:**

```bash
php artisan optimize:clear
```

### 8.2 Database Optimization

**Indexing:**

- Ensure all foreign keys have indexes
- Add indexes on frequently queried columns
- Use composite indexes for multi-column queries

**Query Optimization:**

- Use eager loading to avoid N+1 queries
- Paginate large result sets
- Use select() to limit columns
- Use raw queries for complex operations

**Example:**

```php
// Bad (N+1 query)
$applicants = Applicant::all();
foreach ($applicants as $applicant) {
    echo $applicant->school->name;
}

// Good (eager loading)
$applicants = Applicant::with('school')->get();
foreach ($applicants as $applicant) {
    echo $applicant->school->name;
}
```

### 8.3 Redis Setup (Optional but Recommended)

**Install Redis:**

```bash
sudo apt install redis-server -y
sudo systemctl enable redis-server
sudo systemctl start redis-server
```

**Install PHP Redis Extension:**

```bash
sudo apt install php8.2-redis -y
sudo systemctl restart apache2
```

**Update .env:**

```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### 8.4 PHP Opcache

**Enable Opcache (production):**

Edit `/etc/php/8.2/apache2/php.ini` or `/etc/php/8.2/fpm/php.ini`:

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
opcache.fast_shutdown=1
```

Restart web server:

```bash
sudo systemctl restart apache2
```

### 8.5 Gzip Compression

**Apache:**

```bash
sudo a2enmod deflate
sudo systemctl restart apache2
```

**Nginx:**

```nginx
gzip on;
gzip_vary on;
gzip_min_length 1024;
gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/json;
```

---

## 9. BACKUP & RECOVERY

### 9.1 Database Backup

**Manual Backup:**

```bash
# Backup to file
mysqldump -u pembda_user -p pembda_hub > backup_$(date +%Y%m%d_%H%M%S).sql

# Backup with compression
mysqldump -u pembda_user -p pembda_hub | gzip > backup_$(date +%Y%m%d_%H%M%S).sql.gz
```

**Automated Daily Backup:**

Create backup script `/usr/local/bin/backup-pembdahub.sh`:

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/pembdahub"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="pembda_hub"
DB_USER="pembda_user"
DB_PASS="your_password"

# Create backup directory
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_backup_$DATE.sql.gz

# Files backup
tar -czf $BACKUP_DIR/files_backup_$DATE.tar.gz /var/www/html/pembdahub/storage/app/public

# Delete backups older than 30 days
find $BACKUP_DIR -type f -mtime +30 -delete

echo "Backup completed: $DATE"
```

Make executable:

```bash
sudo chmod +x /usr/local/bin/backup-pembdahub.sh
```

**Setup Cron Job:**

```bash
sudo crontab -e
```

Add line:

```cron
0 2 * * * /usr/local/bin/backup-pembdahub.sh >> /var/log/pembdahub-backup.log 2>&1
```

### 9.2 Restore from Backup

**Database:**

```bash
# Restore from uncompressed backup
mysql -u pembda_user -p pembda_hub < backup.sql

# Restore from compressed backup
gunzip < backup.sql.gz | mysql -u pembda_user -p pembda_hub
```

**Files:**

```bash
# Restore storage files
tar -xzf files_backup.tar.gz -C /
```

---

## 10. MONITORING

### 10.1 Application Monitoring

**Laravel Telescope (Development Only):**

```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

Access at: `http://your-domain.com/telescope`

**Log Files:**

```bash
# Monitor real-time logs
tail -f storage/logs/laravel.log

# Check error logs
grep "ERROR" storage/logs/laravel.log
```

### 10.2 Server Monitoring

**System Resources:**

```bash
# CPU and Memory
htop

# Disk usage
df -h

# MySQL status
sudo systemctl status mysql

# Apache/Nginx status
sudo systemctl status apache2
sudo systemctl status nginx
```

**MySQL Performance:**

```sql
SHOW PROCESSLIST;
SHOW STATUS;
SHOW VARIABLES;
```

### 10.3 Uptime Monitoring

**Recommended Tools:**

- UptimeRobot (free)
- Pingdom
- StatusCake
- Laravel Forge (paid)

---

## 11. TROUBLESHOOTING

### 11.1 Common Issues

**Issue: 500 Internal Server Error**

**Solution:**

```bash
# Check Laravel logs
tail -100 storage/logs/laravel.log

# Check Apache error log
sudo tail -100 /var/log/apache2/error.log

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Check permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

---

**Issue: Database Connection Failed**

**Solution:**

```bash
# Check .env configuration
cat .env | grep DB_

# Test MySQL connection
mysql -u pembda_user -p pembda_hub

# Check MySQL service
sudo systemctl status mysql

# Restart MySQL
sudo systemctl restart mysql
```

---

**Issue: File Upload Not Working**

**Solution:**

```bash
# Check storage link
ls -la public/storage

# Create storage link
php artisan storage:link

# Check permissions
sudo chmod -R 775 storage

# Check PHP upload limits
php -i | grep upload_max_filesize
php -i | grep post_max_size
```

Edit `php.ini`:

```ini
upload_max_filesize = 20M
post_max_size = 20M
```

---

**Issue: WhatsApp Notification Not Sent**

**Solution:**

```bash
# Check .env configuration
cat .env | grep WHATSAPP

# Check logs
tail -100 storage/logs/whatsapp.log
tail -100 storage/logs/laravel.log | grep -i "whatsapp\|fonnte"

# Check queue jobs (WhatsApp uses queue)
php artisan queue:monitor default

# Test API manually
curl -X POST https://api.fonnte.com/send \
  -H "Authorization: YOUR_WHATSAPP_API_TOKEN" \
  -d "target=628123456789" \
  -d "message=Test"
```

---

**Issue: Session Not Persisting**

**Solution:**

```bash
# Check session configuration
cat .env | grep SESSION

# Clear session
php artisan session:table  # if using database
php artisan migrate

# Or use file-based sessions
# Edit .env
SESSION_DRIVER=file
```

---

## 12. MAINTENANCE & DATA MIGRATION

### 12.1 Student Account Synchronization

If you have new student data for other schools or a new academic year, you can synchronize their email accounts and passwords using the following Artisan command:

```bash
php artisan pembda:sync-students
```

**What this command does:**
- Automatically creates user accounts for students who don't have one.
- Corrects email formats to `[firstname]@smpp2.pembdahub.com` (SMP) or `[firstname]@smk.pembdahub.com` (SMK).
- Handles duplicate first names by adding a numeric suffix (e.g., `budi1@...`).
- Resets passwords to the default: `siswasmpsp2` (SMP) or `siswasmks` (SMK).
- Cleans up "orphaned" user accounts that are not linked to any student record.

### 12.2 Teacher Data Migration (Planned)

A similar synchronization process will be implemented for teacher data. Future updates will include:
- Automatic account creation for new teachers.
- Standardized email formatting for staff (`@pembdahub.com`).
- Bulk password reset capabilities for departmental transitions.

---

## CHANGELOG

| Tanggal    | Versi | Perubahan                                              |
| ---------- | ----- | ------------------------------------------------------ |
| 08/02/2026 | 1.0   | Initial deployment guide                               |
| 12/02/2026 | 1.1   | Queue worker, scheduler, security headers, env updates |
| 12/03/2026 | 1.2   | Added Student Sync Artisan command documentation       |

---

**Dokumen dibuat oleh:** Tim Development Pembda Hub  
**Terakhir diupdate:** 12 Maret 2026
