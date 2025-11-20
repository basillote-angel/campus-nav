# ⚠️ Critical Deployment Notes

## Database Configuration Issue Detected

### Problem

Your production `.env` file contains a **configuration mismatch**:

```ini
DB_CONNECTION=mysql        # ❌ Says MySQL
DB_PORT=5432              # ❌ This is PostgreSQL port!
DB_HOST=dpg-d47smhjipnbc73d461g0-a  # ✅ This is a PostgreSQL host (Render.com format)
```

### Analysis

1. **Hostname Format:** `dpg-d47smhjipnbc73d461g0-a` follows the Render.com PostgreSQL database naming pattern (starts with `dpg-`)
2. **Port 5432:** This is the default PostgreSQL port
3. **Connection Type:** `DB_CONNECTION=mysql` is incorrect if you're using PostgreSQL

### Solution

**Before deploying, verify and fix your database configuration:**

#### Option 1: If Using PostgreSQL (Most Likely)

Update your `.env` file:

```ini
DB_CONNECTION=pgsql        # ✅ Change to pgsql
DB_HOST=dpg-d47smhjipnbc73d461g0-a
DB_PORT=5432              # ✅ Correct for PostgreSQL
DB_DATABASE=navistfind_db
DB_USERNAME=navistfind_db_user
DB_PASSWORD=GMi4U3X3vao3qqHpUy6rlSatvXyrkbpj
```

#### Option 2: If Using MySQL/MariaDB

Update your `.env` file:

```ini
DB_CONNECTION=mysql        # ✅ Keep as mysql
DB_HOST=<your-mysql-host>  # ✅ Update to MySQL host
DB_PORT=3306              # ✅ Change to MySQL port
DB_DATABASE=navistfind_db
DB_USERNAME=navistfind_db_user
DB_PASSWORD=<your-mysql-password>
```

### How to Verify Current Database

**On the VPS, test database connection:**

```bash
cd /var/www/navistfind-dashboard

# Test PostgreSQL connection
php artisan tinker
>>> DB::connection()->getPdo();
# If successful, shows PDO connection
# If fails, check error message
>>> exit
```

**Or check what database driver is installed:**

```bash
# Check PHP extensions
php -m | grep -i pdo
php -m | grep -i mysql
php -m | grep -i pgsql

# If pgsql is installed and you're using PostgreSQL, install it:
# sudo apt install php8.2-pgsql
# sudo systemctl restart php8.2-fpm
```

### Recommendation

Based on the hostname format (`dpg-*`), you're **most likely using PostgreSQL**. 

**Action Required:**
1. Verify your actual database type (check Render.com dashboard if using Render)
2. Update `DB_CONNECTION` accordingly:
   - PostgreSQL → `pgsql`
   - MySQL/MariaDB → `mysql` or `mariadb`
3. Ensure correct PHP extension is installed
4. Test connection before deployment

---

## Deployment Pre-Check

Before running the deployment update:

### 1. Database Verification
- [ ] Verify database type (PostgreSQL vs MySQL)
- [ ] Update `DB_CONNECTION` in `.env`
- [ ] Test database connection
- [ ] Ensure PHP extension is installed

### 2. Code Verification
- [ ] Review recent migrations
- [ ] Check for breaking changes
- [ ] Verify composer.json dependencies
- [ ] Test local build (`npm run build`)

### 3. Backup Verification
- [ ] Backup current `.env` file
- [ ] Backup database (if possible)
- [ ] Backup current codebase
- [ ] Document current git commit

### 4. Service Verification
- [ ] PHP-FPM is running
- [ ] Nginx is running
- [ ] Queue workers are running (if applicable)
- [ ] Cron jobs are configured

---

## Common Deployment Issues

### Issue: Database Connection Failed

**Symptoms:**
- 500 Internal Server Error
- Database connection error in logs
- Migration fails

**Solutions:**
```bash
# Check .env configuration
cat /var/www/navistfind-dashboard/.env | grep DB_

# Test database connection
cd /var/www/navistfind-dashboard
php artisan tinker
>>> DB::connection()->getPdo();

# Check if database host is reachable
ping dpg-d47smhjipnbc73d461g0-a
telnet dpg-d47smhjipnbc73d461g0-a 5432
```

### Issue: Migration Fails

**Symptoms:**
- `php artisan migrate` throws errors
- Specific migration fails

**Solutions:**
```bash
# Check migration status
php artisan migrate:status

# Rollback last migration (if needed)
php artisan migrate:rollback --step=1

# Check for migration errors
tail -f storage/logs/laravel.log

# Skip problematic migration temporarily
# php artisan migrate --force --pretend
```

### Issue: Assets Not Loading

**Symptoms:**
- CSS/JS files return 404
- Frontend looks broken
- Vite assets not found

**Solutions:**
```bash
# Rebuild assets
cd /var/www/navistfind-dashboard
npm run build

# Verify build output
ls -la public/build/

# Check Nginx configuration for public path
sudo nginx -t
sudo systemctl reload nginx

# Clear browser cache
```

### Issue: Permission Errors

**Symptoms:**
- Cannot write to storage
- Log files not created
- Upload fails

**Solutions:**
```bash
# Fix ownership
sudo chown -R www-data:www-data /var/www/navistfind-dashboard

# Fix permissions
sudo chmod -R 775 storage bootstrap/cache
sudo chmod 600 .env
```

---

## Post-Deployment Verification

After deployment, verify:

1. **Application Access**
   - [ ] https://navistfind.org loads
   - [ ] https://dashboard.navistfind.org loads
   - [ ] No 500 errors

2. **Database**
   - [ ] Can log in
   - [ ] Can view items
   - [ ] Can create items (test)

3. **File Storage**
   - [ ] Can upload images
   - [ ] Images display correctly
   - [ ] S3/Backblaze B2 integration works

4. **Email**
   - [ ] Emails send correctly
   - [ ] Notifications work
   - [ ] SMTP configuration correct

5. **API**
   - [ ] API endpoints respond
   - [ ] Authentication works
   - [ ] Mobile app can connect

6. **Background Jobs**
   - [ ] Queue processes jobs
   - [ ] Scheduled tasks run
   - [ ] Cron configured correctly

---

## Emergency Contacts

- **VPS Access:** root@72.61.116.160
- **Project Path:** /var/www/navistfind-dashboard
- **Backup Location:** /var/backups/navistfind-dashboard

---

**Last Updated:** 2025-01-15  
**Priority:** ⚠️ CRITICAL - Fix database configuration before deployment

