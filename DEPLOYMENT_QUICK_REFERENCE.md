# Quick Deployment Reference - NavistFind Dashboard

## 🚀 Fast Track Deployment (5 Minutes)

```bash
# SSH to VPS
ssh root@72.61.116.160
# Password: 2qB0eIuwIo5@&xn81aE@

# Navigate to project
cd /var/www/navistfind-dashboard

# Backup current deployment
mkdir -p /var/backups/navistfind-dashboard/$(date +%Y%m%d_%H%M%S)
cp .env /var/backups/navistfind-dashboard/$(date +%Y%m%d_%H%M%S)/.env.backup

# Update code (Git method)
git pull origin main

# OR Update code (Manual method)
# Use SCP from local: scp -r "C:\CAPSTONE PROJECT\campus-nav\*" root@72.61.116.160:/var/www/navistfind-dashboard/

# Install dependencies
composer install --no-dev --optimize-autoloader --no-interaction
npm install --production
npm run build

# Run migrations
php artisan migrate --force

# Clear and rebuild caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Fix permissions
sudo chown -R www-data:www-data /var/www/navistfind-dashboard
sudo chmod -R 775 storage bootstrap/cache
chmod 600 .env

# Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl reload nginx
php artisan queue:restart

# Verify
curl -I https://navistfind.org
```

---

## 🔑 Critical Environment Variables

**Current Production .env Configuration:**

```ini
APP_NAME=Laravel
APP_ENV=production
APP_KEY=base64:TiuUjRvek4sg5Zrp/ndDWwjYcfqaueNAoKLdKEmvqOY=
APP_DEBUG=false
APP_URL=https://navistfind.org

# ⚠️ IMPORTANT: Database Configuration Check Needed
# Your .env shows PostgreSQL port (5432) but DB_CONNECTION=mysql
# Verify which database you're actually using!
DB_CONNECTION=mysql  # Should be 'pgsql' if using PostgreSQL
DB_HOST=dpg-d47smhjipnbc73d461g0-a
DB_PORT=5432
DB_DATABASE=navistfind_db
DB_USERNAME=navistfind_db_user
DB_PASSWORD=GMi4U3X3vao3qqHpUy6rlSatvXyrkbpj

# S3 Storage (Backblaze B2)
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=00555168d4354db0000000001
AWS_SECRET_ACCESS_KEY=K005zzBcjQlVMEnFXJo5WoKZotyhDHK
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=navistfind-storage
AWS_ENDPOINT=https://s3.us-east-1.backblazeb2.com
AWS_USE_PATH_STYLE_ENDPOINT=true

# AI Service
AI_SERVICE_URL=http://127.0.0.1:8001
AI_SERVICE_API_KEY=super-secret-key
AI_BASE_URL=http://127.0.0.1:8001
AI_TOP_K=10
AI_THRESHOLD=0.6

# Email
MAIL_MAILER=smtp
MAIL_HOST=smtp.yourprovider.com
MAIL_PORT=465
MAIL_USERNAME=no-reply@navistfind.org
MAIL_PASSWORD=Navistfind888.
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=no-reply@navistfind.org
MAIL_FROM_NAME="NavistFind Admin"
```

---

## 📋 Pre-Deployment Checklist

- [ ] Backup current deployment
- [ ] Backup `.env` file
- [ ] Backup database (if possible)
- [ ] Verify local code is ready
- [ ] Review recent migrations
- [ ] Check for breaking changes
- [ ] Update documentation

---

## 📍 Key Server Paths

| Item | Path |
|------|------|
| Project Root | `/var/www/navistfind-dashboard` |
| Public Directory | `/var/www/navistfind-dashboard/public` |
| Storage | `/var/www/navistfind-dashboard/storage` |
| Logs | `/var/www/navistfind-dashboard/storage/logs/laravel.log` |
| Nginx Config | `/etc/nginx/sites-available/navistfind-dashboard` |
| PHP-FPM Socket | `/run/php/php8.2-fpm.sock` |
| Backup Directory | `/var/backups/navistfind-dashboard` |

---

## 🔧 Common Commands

### Check Application Status
```bash
# Check PHP-FPM
sudo systemctl status php8.2-fpm

# Check Nginx
sudo systemctl status nginx
sudo nginx -t

# Check Laravel
cd /var/www/navistfind-dashboard
php artisan --version
php artisan route:list | head -20
```

### View Logs
```bash
# Laravel logs
tail -f /var/www/navistfind-dashboard/storage/logs/laravel.log

# Nginx logs
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/nginx/access.log

# PHP-FPM logs
sudo tail -f /var/log/php8.2-fpm.log
```

### Clear Caches
```bash
cd /var/www/navistfind-dashboard
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Queue Management
```bash
# Restart queue workers
php artisan queue:restart

# Check queue status
php artisan queue:work --once --verbose
```

---

## 🚨 Emergency Rollback

```bash
# Stop services
sudo systemctl stop php8.2-fpm
sudo systemctl stop nginx

# Restore from backup
cd /var/www
BACKUP_DATE=$(ls -t /var/backups/navistfind-dashboard | head -1)
tar -xzf /var/backups/navistfind-dashboard/$BACKUP_DATE/code.tar.gz -C navistfind-dashboard-restored
mv navistfind-dashboard navistfind-dashboard-failed
mv navistfind-dashboard-restored navistfind-dashboard
cp /var/backups/navistfind-dashboard/$BACKUP_DATE/.env.backup navistfind-dashboard/.env

# Restart services
sudo systemctl start php8.2-fpm
sudo systemctl start nginx
```

---

## ⚠️ Important Notes

1. **Database Configuration:** Your `.env` shows `DB_CONNECTION=mysql` but `DB_PORT=5432` (PostgreSQL port). Verify which database you're actually using:
   - If PostgreSQL: Change `DB_CONNECTION=pgsql`
   - If MySQL/MariaDB: Change `DB_PORT=3306`

2. **File Storage:** Using S3 (Backblaze B2) for file storage. Ensure credentials are correct.

3. **Queue Workers:** Ensure queue workers are running for background jobs.

4. **Cron Jobs:** Verify Laravel scheduler is configured in crontab.

5. **SSL Certificates:** Certbot handles SSL renewal automatically.

---

## 📞 Support Information

- **VPS IP:** 72.61.116.160
- **Domain:** navistfind.org / dashboard.navistfind.org
- **SSH:** root@72.61.116.160
- **Deployment Path:** /var/www/navistfind-dashboard

---

**Last Updated:** 2025-01-15

