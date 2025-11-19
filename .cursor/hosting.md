   ssh-keygen -t ed25519 -C "you@example.com"
   ```
   Press Enter to accept the default path.
3. Display the public key:
   ```bash
   type $env:USERPROFILE\.ssh\id_ed25519.pub         # PowerShell
   cat ~/.ssh/id_ed25519.pub                         # macOS/Linux
   ```
4. Copy the full key string to clipboard.
5. Back in hPanel, click `Advanced` → `SSH Keys`.
6. Click `Add new`, paste the public key, give it a name like `NavistfindLaptop`, then click `Add`.
7. Wait a few minutes for Hostinger to propagate the key.

### 2.3 Connect to Hostinger via SSH

1. In your terminal:
   ```bash
   ssh username@hostingerServerAddress -p 65002
   ```
   Replace `username` and `hostingerServerAddress` with the values from hPanel.
2. If prompted about fingerprint, type `yes`.

You should now see a prompt similar to `username@server:~$`.

---

## 3. Organize Folders on Hostinger

1. From the SSH session:
   ```bash
   mkdir -p ~/navistfind-admin/shared
   mkdir -p ~/navistfind-admin/releases
   mkdir -p ~/navistfind-admin/shared/storage
   mkdir -p ~/navistfind-admin/shared/scripts
   mkdir -p ~/navistfind-admin/shared/backups
   ```
2. Confirm structure:
   ```bash
   tree -L 2 ~/navistfind-admin
   ```
   If `tree` is unavailable, use:
   ```bash
   find ~/navistfind-admin -maxdepth 2 -type d
   ```

---

## 4. Prepare Laravel Release

### 4.1 Clone Repo Into Temporary Folder

```bash
cd ~/navistfind-admin
git clone https://github.com/basillote-angel/campus-nav.git tempRepo
```

### 4.2 Copy Files Into Timestamped Release

1. Generate a release name:
   ```bash
   releaseName=$(date +"%Y%m%d%H%M%S")
   ```
2. Create the release directory:
   ```bash
   mkdir "releases/$releaseName"
   ```
3. Copy files:
   ```bash
   rsync -av tempRepo/ "releases/$releaseName"
   ```
4. Remove the temporary clone:
   ```bash
   rm -rf tempRepo
   ```

---

## 5. Configure Laravel Environment on Hostinger

### 5.1 Create `.env` in Shared Folder

1. Open nano editor:
   ```bash
   nano ~/navistfind-admin/shared/.env
   ```
2. Paste the template below (update credentials later):

   ```ini
   APP_NAME=Navistfind
   APP_ENV=production
   APP_KEY=
   APP_DEBUG=false
   APP_URL=https://navistfind.org

   LOG_CHANNEL=stack
   LOG_LEVEL=error

   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=navistfind_db
   DB_USERNAME=navistfind_user
   DB_PASSWORD=ChangeThisPassword123!

   BROADCAST_DRIVER=log
   CACHE_DRIVER=file
   FILESYSTEM_DISK=public
   QUEUE_CONNECTION=database
   SESSION_DRIVER=file
   SESSION_LIFETIME=120

   MAIL_MAILER=smtp
   MAIL_HOST=smtp.hostinger.com
   MAIL_PORT=465
   MAIL_USERNAME=no-reply@navistfind.org
   MAIL_PASSWORD=SecureMailPassword!
   MAIL_ENCRYPTION=ssl
   MAIL_FROM_ADDRESS=no-reply@navistfind.org
   MAIL_FROM_NAME="${APP_NAME}"

   MIX_PUSHER_APP_KEY=
   MIX_PUSHER_APP_CLUSTER=mt1
   ```

3. Save (`Ctrl+O`), Enter, exit (`Ctrl+X`).
4. Protect the file:
   ```bash
   chmod 600 ~/navistfind-admin/shared/.env
   ```

### 5.2 Link `.env` and Storage Into Release

```bash
ln -s ~/navistfind-admin/shared/.env ~/navistfind-admin/releases/$releaseName/.env
rm -rf ~/navistfind-admin/releases/$releaseName/storage
ln -s ~/navistfind-admin/shared/storage ~/navistfind-admin/releases/$releaseName/storage
```

---

## 6. Install Laravel Dependencies

1. Move into release:
   ```bash
   cd ~/navistfind-admin/releases/$releaseName
   ```
2. Run composer (Hostinger path varies; use `which composer`). Example:
   ```bash
   php -d memory_limit=-1 /usr/local/bin/composer install --no-dev --optimize-autoloader
   ```
3. Generate key:
   ```bash
   php artisan key:generate --force
   ```
4. Cache optimizations:
   ```bash
   php artisan storage:link
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

If you see memory or permission errors, note the message and adjust accordingly.

---

## 7. Set Up MySQL Database in Hostinger

1. In hPanel, click `Hosting` → `Manage`.
2. Under `Databases`, click `MySQL Databases`.
3. Click `Create Database`.
   - Database name: `navistfind_db`
   - Username: `navistfind_user`
   - Password: `ChangeThisPassword123!` (use a strong unique password; update `.env` to match).
4. Copy the database details. Update `.env` if necessary.
5. Back in SSH:
   ```bash
   cd ~/navistfind-admin/releases/$releaseName
   php artisan migrate --force
   php artisan db:seed --force
   ```
6. Basic DB connectivity check:
   ```bash
   php artisan tinker
   >>> DB::connection()->getPdo();
   exit
   ```

---

## 8. Point `public_html` to Release

1. Backup existing `public_html`:
   ```bash
   mv ~/public_html ~/public_html_backup_$(date +"%Y%m%d%H%M%S")
   ```
2. Link new release:
   ```bash
   ln -s ~/navistfind-admin/releases/$releaseName/public ~/public_html
   ```
3. Verify:
   ```bash
   ls -l ~ | grep public_html
   ```

Optional: create a `current` symlink for future deployments.

```bash
ln -s ~/navistfind-admin/releases/$releaseName ~/navistfind-admin/releases/current
```

---

## 9. Adjust Permissions on Hostinger

```bash
find ~/navistfind-admin/shared/storage -type d -exec chmod 775 {} \;
find ~/navistfind-admin/shared/storage -type f -exec chmod 664 {} \;
chmod 775 ~/navistfind-admin/releases/$releaseName/bootstrap/cache
```

---

## 10. Configure Cron for Laravel Scheduler

1. In hPanel, click `Advanced` → `Cron Jobs`.
2. Click `Add cron job`.
   - Command:
     - Frequency: Every minute (`* * * * *`).
3. Replace `username` with your Hostinger username.

---

## 11. SSL for `navistfind.org`

1. In hPanel, click `SSL`.
2. Locate `navistfind.org` and click `Set up`.
3. Choose the free Hostinger SSL and follow prompts.
4. After installation, confirm `.htaccess` in `public/` forces HTTPS:

   ```apacheconf
   <IfModule mod_rewrite.c>
       RewriteEngine On
       RewriteCond %{HTTPS} !=on
       RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   </IfModule>
   ```

---

## 12. Verify Laravel Deployment

1. Open a browser and visit `https://navistfind.org`.
2. If you see errors, temporarily set `APP_DEBUG=true` in `.env`, reload, then set back to `false`.
3. Check response headers:
   ```bash
   curl -I https://navistfind.org
   ```
4. Monitor logs:
   ```bash
   tail -f ~/navistfind-admin/shared/storage/logs/laravel.log
   ```

---

## 13. Prepare Ubuntu VPS for FastAPI

### 13.1 Initial SSH Login

```bash
ssh root@yourVpsIp
```
Replace `yourVpsIp` with the actual server IP.

### 13.2 Create Non-Root User

```bash
adduser navistfindadmin
usermod -aG sudo navistfindadmin
```

Set a strong password when prompted.

### 13.3 Configure SSH Key for New User

1. Copy your local public key (same as Hostinger) to the VPS:
   ```bash
   ssh-copy-id navistfindadmin@yourVpsIp
   ```
   If `ssh-copy-id` is not available (on Windows PowerShell), manually add:
   ```bash
   ssh navistfindadmin@yourVpsIp
   mkdir -p ~/.ssh && chmod 700 ~/.ssh
   echo "ssh-ed25519 AAAA..." >> ~/.ssh/authorized_keys
   chmod 600 ~/.ssh/authorized_keys
   ```

### 13.4 Harden SSH

1. Edit SSH config:
   ```bash
   sudo nano /etc/ssh/sshd_config
   ```
2. Set:
   ```
   PermitRootLogin no
   PasswordAuthentication no
   ```
3. Save, exit, restart sshd:
   ```bash
   sudo systemctl reload sshd
   ```

Re-connect using `ssh navistfindadmin@yourVpsIp` to verify.

---

## 14. Install VPS Dependencies

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y python3.10-venv python3-pip nginx git ufw curl
```

---

## 15. Clone FastAPI Repository

```bash
cd /var/www
sudo mkdir navistfind-ai-service
sudo chown navistfindadmin:navistfindadmin navistfind-ai-service
cd navistfind-ai-service
git clone https://github.com/basillote-angel/navistfind-ai-service.git .
```

---

## 16. Python Virtual Environment

```bash
python3 -m venv venv
source venv/bin/activate
pip install --upgrade pip
pip install -r requirements.txt
```

Keep the virtual environment active while configuring.

---

## 17. FastAPI `.env` Configuration

1. Create `.env`:
   ```bash
   nano /var/www/navistfind-ai-service/.env
   ```
2. Paste:

   ```ini
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_NAME=navistfind_ai_db
   DB_USER=navistfind_ai_user
   DB_PASS=ChangeAiDbPassword123!

   MODEL_PATH=models/sbert_model/
   SECRET_KEY=ChangeThisSecretKey123!
   ALLOWED_ORIGINS=https://navistfind.org,https://app.navistfind.org

   LOG_LEVEL=info
   ```
3. Save, exit.
4. Protect the file:
   ```bash
   chmod 600 /var/www/navistfind-ai-service/.env
   ```

---

## 18. Database for FastAPI (if required)

If FastAPI uses MySQL:

```bash
sudo apt install mysql-server -y
sudo mysql_secure_installation
sudo mysql -u root -p
```

In MySQL shell:

```sql
CREATE DATABASE navistfind_ai_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'navistfind_ai_user'@'localhost' IDENTIFIED BY 'ChangeAiDbPassword123!';
GRANT ALL PRIVILEGES ON navistfind_ai_db.* TO 'navistfind_ai_user'@'localhost';
FLUSH PRIVILEGES;
```

Exit MySQL with `EXIT;`.

---

## 19. Create Uvicorn Systemd Service

1. Create service file:
   ```bash
   sudo nano /etc/systemd/system/navistfind-ai.service
   ```
2. Paste:

   ```ini
   [Unit]
   Description=Navistfind FastAPI Service
   After=network.target

   [Service]
   User=navistfindadmin
   Group=www-data
   WorkingDirectory=/var/www/navistfind-ai-service
   Environment="PATH=/var/www/navistfind-ai-service/venv/bin"
   ExecStart=/var/www/navistfind-ai-service/venv/bin/uvicorn app.main:app --host 127.0.0.1 --port 8000 --workers 3
   Restart=always
   RestartSec=5
   StandardOutput=journal
   StandardError=journal

   [Install]
   WantedBy=multi-user.target
   ```

3. Save, exit.
4. Enable service:
   ```bash
   sudo systemctl daemon-reload
   sudo systemctl enable navistfind-ai
   sudo systemctl start navistfind-ai
   sudo systemctl status navistfind-ai
   ```

Ensure status shows `active (running)`.

---

## 20. Configure Nginx Reverse Proxy

1. Create site config:
   ```bash
   sudo nano /etc/nginx/sites-available/navistfind-ai
   ```
2. Paste:

   ```nginx
   server {
       listen 80;
       server_name api.navistfind.org;

       location / {
           proxy_pass http://127.0.0.1:8000;
           proxy_set_header Host $host;
           proxy_set_header X-Real-IP $remote_addr;
           proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
           proxy_set_header X-Forwarded-Proto $scheme;
       }

       access_log /var/log/nginx/navistfind-ai-access.log;
       error_log /var/log/nginx/navistfind-ai-error.log;
   }
   ```

3. Enable site:
   ```bash
   sudo ln -s /etc/nginx/sites-available/navistfind-ai /etc/nginx/sites-enabled/
   sudo nginx -t
   sudo systemctl reload nginx
   ```

---

## 21. Configure Firewall on VPS

```bash
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw enable
sudo ufw status
```

---

## 22. DNS Configuration for `navistfind.org`

1. In the Hostinger hPanel, click `Domains` → `navistfind.org` → `DNS / Nameservers`.
2. Add or confirm records:
   - `A` record: Host `@`, points to Hostinger shared hosting IP.
   - `A` record: Host `www`, points to Hostinger IP, or set CNAME `www` → `navistfind.org`.
   - `A` record: Host `api`, points to VPS IP.
3. Save changes and wait for DNS propagation (can take up to 30 minutes, often faster).

---

## 23. SSL for FastAPI (`api.navistfind.org`)

1. On VPS, install Certbot:
   ```bash
   sudo apt install certbot python3-certbot-nginx -y
   ```
2. Run:
   ```bash
   sudo certbot --nginx -d api.navistfind.org
   ```
3. Follow prompts, agree to terms, choose redirect option.
4. Test renewal:
   ```bash
   sudo certbot renew --dry-run
   ```

Nginx config now listens on port 443 with SSL automatically.

---

## 24. Verify FastAPI Deployment

1. Local request (on VPS):
   ```bash
   curl http://127.0.0.1:8000/docs
   ```
2. External request:
   ```bash
   curl https://api.navistfind.org/health
   ```
3. Check logs:
   ```bash
   sudo journalctl -u navistfind-ai -f
   sudo tail -f /var/log/nginx/navistfind-ai-error.log
   ```

---

## 25. Flutter Mobile App Integration

### 25.1 Configure API Endpoints

In Flutter project (example `lib/config/apiConfig.dart`):

```dart
class ApiConfig {
  static const String adminBaseUrl = 'https://navistfind.org/api';
  static const String aiBaseUrl = 'https://api.navistfind.org';
}
```

Ensure camelCase naming conventions and descriptive variable names.

### 25.2 Environment-Specific Values

If using flavors, create `lib/environment.dart`:

```dart
class Environment {
  static const String laravelApi = 'https://navistfind.org/api';
  static const String fastApi = 'https://api.navistfind.org';
  static const String mapboxKey = 'YOUR_MAPBOX_KEY';
}
```

### 25.3 Test from Flutter

Add a network test function:

```dart
Future<void> testEndpoints() async {
  final adminResponse = await http.get(Uri.parse('${Environment.laravelApi}/ping'));
  final aiResponse = await http.get(Uri.parse('${Environment.fastApi}/health'));
  debugPrint('Admin: ${adminResponse.statusCode}');
  debugPrint('AI: ${aiResponse.statusCode}');
}
```

Ensure FastAPI `ALLOWED_ORIGINS` includes your Flutter app domain or scheme (e.g., `https://navistfind.org`, `https://app.navistfind.org`).

---

## 26. Continuous Deployment Workflow

### 26.1 Laravel Updates

1. SSH to Hostinger.
2. Create new release (`releaseName=$(date +"%Y%m%d%H%M%S")`).
3. `git clone` into temp folder, rsync to release.
4. Link `.env` and storage.
5. Run composer, artisan commands.
6. Run migrations (`php artisan migrate --force`).
7. Update `current` symlink to new release.
8. Visit `https://navistfind.org` to test.
9. If issues arise, switch symlink back to previous release.

### 26.2 FastAPI Updates

1. SSH to VPS as `navistfindadmin`.
2. `cd /var/www/navistfind-ai-service`.
3. `source venv/bin/activate`.
4. `git pull`.
5. `pip install -r requirements.txt`.
6. Restart service:
   ```bash
   sudo systemctl restart navistfind-ai
   ```
7. Check status/logs, test `https://api.navistfind.org/health`.

---

## 27. Automated Backups on Hostinger

1. Create `~/navistfind-admin/shared/scripts/dbBackup.sh`:
   ```bash
   nano ~/navistfind-admin/shared/scripts/dbBackup.sh
   ```
2. Paste:
   ```bash
   #!/bin/bash
   timestamp=$(date +"%Y%m%d%H%M%S")
   mysqldump -u navistfind_user -p'SecurePasswordHere' navistfind_db > ~/navistfind-admin/shared/backups/navistfind_db_$timestamp.sql
   find ~/navistfind-admin/shared/backups -type f -mtime +14 -delete
   ```
3. Save, exit.
4. Make executable:
   ```bash
   chmod +x ~/navistfind-admin/shared/scripts/dbBackup.sh
   ```
5. In hPanel, `Advanced` → `Cron Jobs` → add:
   ```
   0 2 * * * /home/username/navistfind-admin/shared/scripts/dbBackup.sh
   ```
   Replace credentials accordingly.

---

## 28. Log Rotation on VPS

Create `/etc/logrotate.d/navistfind-ai`:

```bash
sudo nano /etc/logrotate.d/navistfind-ai
```

Paste:

```
/var/log/nginx/navistfind-ai-*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 640 www-data adm
    sharedscripts
    postrotate
        [ -s /run/nginx.pid ] && kill -USR1 $(cat /run/nginx.pid)
    endscript
}

/usr/bin/journalctl -u navistfind-ai --vacuum-time=14d
```

Save, exit.

---

## 29. Troubleshooting Guide

- **Laravel showing blank page**: Check `storage/logs/laravel.log`. Ensure `APP_KEY` is set. Temporarily set `APP_DEBUG=true`.
- **500 error on Laravel**: Run `php artisan config:clear`.
- **composer memory errors**: Use `php -d memory_limit=-1`.
- **FastAPI 502/Bad Gateway**: Confirm `sudo systemctl status navistfind-ai`. Ensure Nginx proxy is linked.
- **SSL not active**: Re-run `certbot --nginx -d api.navistfind.org`. For main domain, reinstall via hPanel.
- **CORS issues**: Update `ALLOWED_ORIGINS` in FastAPI `.env`.
- **Database connection error**: Verify MySQL credentials and host.
- **Cron not running**: Check Hostinger cron logs in hPanel `Cron Jobs` page.

---

## 30. Security Best Practices

- Use unique, strong passwords; rotate quarterly.
- Keep `.env` files outside web-accessible directories (already handled via symlink).
- Limit file permissions (use `chmod 600` for secrets).
- Enable two-factor authentication on Hostinger, GitHub, VPS provider.
- Schedule regular OS and package updates.
- Monitor logs weekly (`tail -f` on logs, `journalctl` for systemd service).
- Consider fail2ban on VPS for SSH hardening (optional).

---

## 31. Final Verification Checklist

- [ ] `https://navistfind.org` loads correctly via HTTPS.
- [ ] `https://api.navistfind.org/docs` reachable and secured.
- [ ] Database migrations completed without errors.
- [ ] Cron job output in `scheduler.log`.
- [ ] Flutter app communicates with both APIs successfully.
- [ ] Automated backups run (check backup directory).
- [ ] Nginx access/error logs rotating.

---

## 32. Monitoring and Next Steps

- Set up uptime monitoring (UptimeRobot or similar) for `navistfind.org` and `api.navistfind.org`.
- Document deployment workflow for team members.
- Schedule regular backups and verify restore process.
- Plan future enhancements (CDN, caching, infrastructure scaling if needed).

---

**Save this entire content as `navistfind_deployment_guide.md`.**