# Deployment Update Guide - NavistFind Dashboard

**Project:** campus-nav  
**VPS:** `72.61.116.160` (srv1117384.hstgr.cloud)  
**Deployment Path:** `/var/www/navistfind-dashboard`  
**Domain:** `https://dashboard.navistfind.org` (or `https://navistfind.org`)

---

## Prerequisites

- ✅ SSH access to VPS: `root@72.61.116.160`
- ✅ SSH Password: `2qB0eIuwIo5@&xn81aE@`
- ✅ Local project path: `C:\CAPSTONE PROJECT\campus-nav`
- ✅ Current production `.env` values (provided above)
- ✅ Git repository access (if code is versioned)

---

## Step-by-Step Update Process

### Phase 1: Pre-Deployment Backup

**Goal:** Safeguard current production deployment before making changes.

#### 1.1 Connect to VPS
```bash
ssh root@72.61.116.160
# Enter password: 2qB0eIuwIo5@&xn81aE@
```

#### 1.2 Create Backup Directory
```bash
mkdir -p /var/backups/navistfind-dashboard
cd /var/backups/navistfind-dashboard
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
mkdir -p backup_$TIMESTAMP
```

#### 1.3 Backup Current Deployment
```bash
# Backup current code (excluding vendor, node_modules, storage)
cd /var/www/navistfind-dashboard
tar -czf /var/backups/navistfind-dashboard/backup_$TIMESTAMP/code.tar.gz \
  --exclude='vendor' \
  --exclude='node_modules' \
  --exclude='storage/logs' \
  --exclude='storage/framework/cache' \
  --exclude='storage/framework/sessions' \
  --exclude='storage/framework/views' \
  --exclude='.git' \
  .

# Backup .env file
cp .env /var/backups/navistfind-dashboard/backup_$TIMESTAMP/.env.backup

# Backup database (if PostgreSQL is accessible)
# Note: Update connection details as needed
pg_dump -h dpg-d47smhjipnbc73d461g0-a -p 5432 -U navistfind_db_user -d navistfind_db > /var/backups/navistfind-dashboard/backup_$TIMESTAMP/database.sql
# Enter password when prompted: GMi4U3X3vao3qqHpUy6rlSatvXyrkbpj
```

#### 1.4 Document Current State
```bash
# Save current git commit hash (if applicable)
cd /var/www/navistfind-dashboard
git log -1 --oneline > /var/backups/navistfind-dashboard/backup_$TIMESTAMP/git_commit.txt 2>/dev/null || echo "No git repo" > /var/backups/navistfind-dashboard/backup_$TIMESTAMP/git_commit.txt

# Save current Laravel version
php artisan --version > /var/backups/navistfind-dashboard/backup_$TIMESTAMP/laravel_version.txt
```

**✅ Confirm Phase 1 complete before proceeding.**

---

### Phase 2: Prepare Local Code for Deployment

**Goal:** Ensure local code is ready and create deployment package.

#### 2.1 Verify Local Environment
```powershell
# In PowerShell, navigate to project
cd "C:\CAPSTONE PROJECT\campus-nav"

# Check git status
git status

# Ensure .env.example exists and is up to date
# Ensure composer.json and package.json are committed
```

#### 2.2 Test Local Build
```powershell
# Install dependencies (if not already done)
composer install --optimize-autoloader --no-dev
npm install
npm run build

# Run tests if available
# php artisan test
```

#### 2.3 Create Deployment Checklist
Document any new migrations, config changes, or environment variables needed.

**✅ Confirm Phase 2 complete before proceeding.**

---

### Phase 3: Transfer Code to VPS

**Goal:** Upload updated code to VPS.

#### Option A: Using Git (Recommended if repo is accessible)

```bash
# On VPS
cd /var/www/navistfind-dashboard

# Stash or backup current changes
git stash 2>/dev/null || echo "No git repo or changes"

# Pull latest code
git pull origin main  # or master, depending on your branch

# If there are conflicts, resolve them manually
```

#### Option B: Using SCP/rsync (If Git is not available)

```powershell
# From Windows PowerShell (in project root)
# First, ensure you have SSH access configured

# Option B1: Using SCP (simple copy)
scp -r "C:\CAPSTONE PROJECT\campus-nav\*" root@72.61.116.160:/var/www/navistfind-dashboard-temp/

# Option B2: Using rsync (more efficient, requires WSL or Git Bash)
# rsync -avz --exclude 'vendor' --exclude 'node_modules' --exclude '.git' \
#   --exclude 'storage/logs' --exclude 'storage/framework' \
#   "C:\CAPSTONE PROJECT\campus-nav\" root@72.61.116.160:/var/www/navistfind-dashboard-temp/
```

**If using SCP/rsync, continue with:**

```bash
# On VPS
cd /var/www
mv navistfind-dashboard navistfind-dashboard-old
mv navistfind-dashboard-temp navistfind-dashboard

# Copy preserved files
cp navistfind-dashboard-old/.env navistfind-dashboard/.env
cp -r navistfind-dashboard-old/storage/app/* navistfind-dashboard/storage/app/ 2>/dev/null || true
```

**✅ Confirm Phase 3 complete before proceeding.**

---

### Phase 4: Update Dependencies and Environment

**Goal:** Install/update Composer and NPM dependencies, preserve `.env`.

#### 4.1 Preserve Environment Configuration
```bash
# On VPS
cd /var/www/navistfind-dashboard

# Verify .env exists and contains production values
ls -la .env
# If .env was overwritten, restore from backup:
# cp /var/backups/navistfind-dashboard/backup_*/env.backup .env

# Update .env with provided values if needed (see below)
```

#### 4.2 Update .env File (if needed)

```bash
nano .env
```

**Verify/Update these critical values:**
```ini
APP_NAME=Laravel
APP_ENV=production
APP_KEY=base64:TiuUjRvek4sg5Zrp/ndDWwjYcfqaueNAoKLdKEmvqOY=
APP_DEBUG=false
APP_URL=https://navistfind.org

DB_CONNECTION=mysql
DB_HOST=dpg-d47smhjipnbc73d461g0-a
DB_PORT=5432
DB_DATABASE=navistfind_db
DB_USERNAME=navistfind_db_user
DB_PASSWORD=GMi4U3X3vao3qqHpUy6rlSatvXyrkbpj

# ... (rest of your .env values)
```

**Note:** Your .env shows PostgreSQL port 5432 but `DB_CONNECTION=mysql`. Verify which database you're actually using. If PostgreSQL, set:
```ini
DB_CONNECTION=pgsql
```

#### 4.3 Install Composer Dependencies
```bash
cd /var/www/navistfind-dashboard

# Update Composer dependencies
composer install --no-dev --optimize-autoloader --no-interaction

# If composer not in PATH:
# php /usr/local/bin/composer install --no-dev --optimize-autoloader --no-interaction
```

#### 4.4 Install NPM Dependencies and Build Assets
```bash
cd /var/www/navistfind-dashboard

# Install Node dependencies
npm install --production

# Build frontend assets
npm run build

# Verify build output
ls -la public/build/
```

**✅ Confirm Phase 4 complete before proceeding.**

---

### Phase 5: Database Migrations and Cache

**Goal:** Apply database changes and optimize Laravel caches.

#### 5.1 Run Database Migrations
```bash
cd /var/www/navistfind-dashboard

# Check migration status
php artisan migrate:status

# Run new migrations
php artisan migrate --force

# If migrations fail, check logs
tail -f storage/logs/laravel.log
```

#### 5.2 Clear and Rebuild Caches
```bash
cd /var/www/navistfind-dashboard

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild optimized caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache  # If available

# Generate application key if needed (should not run if APP_KEY exists)
# php artisan key:generate --force
```

#### 5.3 Update Storage Link (if needed)
```bash
php artisan storage:link
```

**✅ Confirm Phase 5 complete before proceeding.**

---

### Phase 6: Set File Permissions

**Goal:** Ensure proper file ownership and permissions.

#### 6.1 Fix Ownership
```bash
cd /var/www/navistfind-dashboard

# Set ownership to www-data (web server user)
sudo chown -R www-data:www-data /var/www/navistfind-dashboard

# Set specific permissions
sudo find /var/www/navistfind-dashboard -type f -exec chmod 644 {} \;
sudo find /var/www/navistfind-dashboard -type d -exec chmod 755 {} \;

# Make storage and cache writable
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
```

#### 6.2 Protect Sensitive Files
```bash
chmod 600 .env
chmod 600 storage/*.key 2>/dev/null || true
```

**✅ Confirm Phase 6 complete before proceeding.**

---

### Phase 7: Restart Services

**Goal:** Ensure all services are running with updated code.

#### 7.1 Restart PHP-FPM
```bash
# Restart PHP-FPM to clear opcache
sudo systemctl restart php8.2-fpm
# Or if using different version:
# sudo systemctl restart php-fpm

# Check status
sudo systemctl status php8.2-fpm
```

#### 7.2 Reload Nginx
```bash
# Test Nginx configuration
sudo nginx -t

# If test passes, reload
sudo systemctl reload nginx

# Check status
sudo systemctl status nginx
```

#### 7.3 Restart Queue Workers (if running)
```bash
# Check for running queue workers
ps aux | grep "queue:work"

# If using systemd for queues:
# sudo systemctl restart laravel-worker
# Or supervisor:
# sudo supervisorctl restart laravel-worker:*

# Restart queue manually if needed
cd /var/www/navistfind-dashboard
php artisan queue:restart
```

#### 7.4 Verify Laravel Scheduler (Cron)
```bash
# Check if cron is configured
crontab -l

# Should include:
# * * * * * cd /var/www/navistfind-dashboard && php artisan schedule:run >> /dev/null 2>&1

# If not configured, add it:
# crontab -e
# Add: * * * * * cd /var/www/navistfind-dashboard && php artisan schedule:run >> /dev/null 2>&1
```

**✅ Confirm Phase 7 complete before proceeding.**

---

### Phase 8: Post-Deployment Verification

**Goal:** Verify deployment is successful and application is working.

#### 8.1 Check Application Health
```bash
# On VPS
curl -I https://navistfind.org
# Should return 200 OK or 301/302 redirect

curl -I https://dashboard.navistfind.org
# Should return 200 OK

# Check API endpoint
curl -I https://navistfind.org/api/health
# Or check /up endpoint
curl https://navistfind.org/up
```

#### 8.2 Verify Database Connection
```bash
cd /var/www/navistfind-dashboard
php artisan tinker
>>> DB::connection()->getPdo();
>>> exit
```

#### 8.3 Check Logs
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Check Nginx error logs
sudo tail -f /var/log/nginx/error.log

# Check PHP-FPM logs
sudo tail -f /var/log/php8.2-fpm.log
```

#### 8.4 Test Critical Functionality
1. **Visit Dashboard:** `https://dashboard.navistfind.org` (or `https://navistfind.org`)
2. **Test Login:** Verify admin/staff can log in
3. **Test API:** Make API calls from mobile app or Postman
4. **Test File Uploads:** Verify S3/Backblaze B2 integration works
5. **Test Notifications:** Verify email/push notifications work

#### 8.5 Monitor for Errors
```bash
# Watch logs for 5 minutes
timeout 300 tail -f storage/logs/laravel.log
```

**✅ Confirm Phase 8 complete before proceeding.**

---

### Phase 9: Rollback Plan (If Issues Occur)

**Goal:** Be prepared to quickly revert if deployment fails.

#### 9.1 Quick Rollback Steps
```bash
# Stop services temporarily
sudo systemctl stop php8.2-fpm
sudo systemctl stop nginx

# Restore from backup
cd /var/www
mv navistfind-dashboard navistfind-dashboard-failed
tar -xzf /var/backups/navistfind-dashboard/backup_*/code.tar.gz -C navistfind-dashboard-restored
mv navistfind-dashboard-restored navistfind-dashboard

# Restore .env
cp /var/backups/navistfind-dashboard/backup_*/.env.backup navistfind-dashboard/.env

# Restore permissions
sudo chown -R www-data:www-data /var/www/navistfind-dashboard
sudo chmod -R 775 storage bootstrap/cache

# Restart services
sudo systemctl start php8.2-fpm
sudo systemctl start nginx

# Clear caches
cd /var/www/navistfind-dashboard
php artisan cache:clear
php artisan config:clear
```

#### 9.2 Database Rollback (if migrations caused issues)
```bash
# Restore database backup
pg_restore -h dpg-d47smhjipnbc73d461g0-a -p 5432 -U navistfind_db_user -d navistfind_db /var/backups/navistfind-dashboard/backup_*/database.sql
```

---

### Phase 10: Cleanup

**Goal:** Remove old backups and temporary files.

#### 10.1 Archive Old Backups (keep last 5)
```bash
cd /var/backups/navistfind-dashboard
ls -t | tail -n +6 | xargs rm -rf 2>/dev/null || true
```

#### 10.2 Remove Temporary Files
```bash
# Remove old deployment directory if exists
rm -rf /var/www/navistfind-dashboard-old 2>/dev/null || true
rm -rf /var/www/navistfind-dashboard-temp 2>/dev/null || true
```

---

## Quick Deployment Script (All-in-One)

If you prefer an automated approach, here's a consolidated script:

```bash
#!/bin/bash
# Save as: /var/www/update-navistfind.sh
# Run with: sudo bash /var/www/update-navistfind.sh

set -e  # Exit on error

PROJECT_DIR="/var/www/navistfind-dashboard"
BACKUP_DIR="/var/backups/navistfind-dashboard"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")

echo "=== Starting Deployment Update ==="

# Phase 1: Backup
echo "[1/8] Creating backup..."
mkdir -p "$BACKUP_DIR/backup_$TIMESTAMP"
cd "$PROJECT_DIR"
cp .env "$BACKUP_DIR/backup_$TIMESTAMP/.env.backup"
tar -czf "$BACKUP_DIR/backup_$TIMESTAMP/code.tar.gz" \
  --exclude='vendor' --exclude='node_modules' --exclude='storage/logs' \
  --exclude='storage/framework' --exclude='.git' .

# Phase 2: Update code (if using git)
echo "[2/8] Updating code from Git..."
cd "$PROJECT_DIR"
git pull origin main || echo "Git pull failed or not a git repo"

# Phase 3: Install dependencies
echo "[3/8] Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "[4/8] Installing NPM dependencies and building assets..."
npm install --production
npm run build

# Phase 4: Run migrations
echo "[5/8] Running database migrations..."
php artisan migrate --force

# Phase 5: Clear and rebuild caches
echo "[6/8] Clearing and rebuilding caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Phase 6: Fix permissions
echo "[7/8] Fixing file permissions..."
sudo chown -R www-data:www-data "$PROJECT_DIR"
sudo chmod -R 775 storage bootstrap/cache
chmod 600 .env

# Phase 7: Restart services
echo "[8/8] Restarting services..."
sudo systemctl restart php8.2-fpm
sudo systemctl reload nginx
php artisan queue:restart

echo "=== Deployment Complete ==="
echo "Backup saved to: $BACKUP_DIR/backup_$TIMESTAMP"
echo "Please verify the application at: https://navistfind.org"
```

---

## Troubleshooting

### Issue: Permission Denied Errors
```bash
sudo chown -R www-data:www-data /var/www/navistfind-dashboard
sudo chmod -R 775 storage bootstrap/cache
```

### Issue: Composer Memory Error
```bash
php -d memory_limit=-1 /usr/local/bin/composer install --no-dev --optimize-autoloader
```

### Issue: NPM Build Fails
```bash
# Clear npm cache
npm cache clean --force
# Reinstall
rm -rf node_modules package-lock.json
npm install
npm run build
```

### Issue: Database Connection Failed
- Verify `.env` database credentials
- Check if database host is accessible: `telnet dpg-d47smhjipnbc73d461g0-a 5432`
- Verify `DB_CONNECTION` matches actual database type (mysql vs pgsql)

### Issue: 500 Internal Server Error
```bash
# Enable debug temporarily
sed -i 's/APP_DEBUG=false/APP_DEBUG=true/' .env
php artisan config:clear

# Check logs
tail -f storage/logs/laravel.log

# After debugging, set back to false
sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' .env
php artisan config:cache
```

### Issue: Assets Not Loading (404)
```bash
# Rebuild assets
npm run build
# Verify public/build exists
ls -la public/build/
```

---

## Deployment Checklist

Use this checklist to track your deployment:

- [ ] Phase 1: Backup completed
- [ ] Phase 2: Local code prepared
- [ ] Phase 3: Code transferred to VPS
- [ ] Phase 4: Dependencies updated
- [ ] Phase 5: Migrations run successfully
- [ ] Phase 6: Permissions set correctly
- [ ] Phase 7: Services restarted
- [ ] Phase 8: Application verified
- [ ] Phase 9: Rollback plan ready
- [ ] Phase 10: Cleanup completed

---

## Post-Deployment Notes

1. **Monitor logs** for the next 24 hours
2. **Check queue workers** are processing jobs
3. **Verify scheduled tasks** are running (cron)
4. **Test critical user flows** (login, item creation, notifications)
5. **Monitor server resources** (CPU, memory, disk)

---

## Next Steps

- Schedule regular backups
- Set up monitoring/alerting
- Document any configuration changes
- Update deployment logs in `.cursor/hosting_operations_log.md`

---

**Last Updated:** 2025-01-15  
**VPS:** 72.61.116.160  
**Project:** campus-nav (NavistFind Dashboard)

