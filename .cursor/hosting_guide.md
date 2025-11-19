## Hosting Guide Overview

Welcome! This guide walks you through deploying the entire NavistFind project on Hostinger VPS. Everything is split into phases so you can focus on one area at a time. For each task you will see:

- **Manual step** – you must do this yourself in Hostinger or on your machine.
- **Cursor step** – Cursor can run the commands or edit files for you. Just ask when you are ready.
- **Tip / Troubleshooting** – extra context if something goes wrong.

> ✅ **Before moving ahead:** Finish every checklist in the current phase, then confirm it’s done. Only start the next phase after confirmation.

---

## Phase 0 – Gather Accounts and Access

Goal: Ensure you can log in everywhere and that your local machine can access the VPS.

- **Manual step:** Confirm Hostinger account access and that the VPS (`srv1117384.hstgr.cloud`) is active.
  - Hostinger hPanel → `Hosting` → select your VPS plan.
  - Note the root password or confirm your SSH key is added (Hostinger > VPS > `SSH keys`).
- **Manual step:** Find your domain registrar (Namecheap) credentials to update DNS later.
- **Manual step:** Verify you can log in to the VPS via SSH:
  ```powershell
  ssh root@srv1117384.hstgr.cloud
  ```
  - If you set up the non-root sudo user (`navistfindadmin`), also test:
  ```powershell
  ssh navistfindadmin@srv1117384.hstgr.cloud
  ```
- **Cursor step:** When you confirm SSH access works, Cursor can help run server-side commands.

> ❗ Tip: If SSH says “Permission denied,” make sure your local private key matches the one uploaded to Hostinger. On Windows, the default key lives at `C:\Users\<you>\.ssh\id_ed25519`.

✅ **Confirm Phase 0 complete** before moving on.

---

## Phase 1 – VPS Preparation (System Updates & Packages)

Goal: Update Ubuntu 24.04, install base packages, and set up the server for Laravel + FastAPI.

Checklist:
1. Update and upgrade packages.
2. Install essentials: Git, Python, Node, Nginx, Certbot, PHP stack, MariaDB.
3. Create deployment directories.

### 1. Update packages
- **Manual / Cursor:** Ask Cursor to run:
  ```bash
  sudo apt update && sudo apt upgrade -y
  sudo apt install -y software-properties-common ufw fail2ban unzip
  ```
- **Manual step:** Reboot if the update installs a kernel upgrade: `sudo reboot`.

### 2. Install language runtimes
- **Cursor step:** Python (for FastAPI):
  ```bash
  sudo apt install -y python3 python3-venv python3-pip
  ```
- **Cursor step:** Node.js (for building Flutter web or tooling, optional but useful):
  ```bash
  curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
  sudo apt install -y nodejs
  ```
- **Cursor step:** PHP & extensions for Laravel:
  ```bash
  sudo add-apt-repository ppa:ondrej/php -y
  sudo apt update
  sudo apt install -y php8.2 php8.2-fpm php8.2-cli php8.2-mbstring php8.2-xml php8.2-mysql php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath
  ```

### 3. Install web server & database
- **Cursor step:** Nginx + Certbot:
  ```bash
  sudo apt install -y nginx
  sudo apt install -y certbot python3-certbot-nginx
  ```
- **Cursor step:** MariaDB (MySQL compatible):
  ```bash
  sudo apt install -y mariadb-server mariadb-client
  sudo systemctl enable --now mariadb
  ```
- **Manual step:** Secure MariaDB:
  ```bash
  sudo mysql_secure_installation
  ```
  - Answer prompts: `Y` for root password, set a strong password, remove anonymous users, disallow root remote login, remove test database, reload privileges.

### 4. Create project directories
- **Cursor step:** 
  ```bash
  sudo mkdir -p /var/www/navistfind-ai-service
  sudo mkdir -p /var/www/navistfind-dashboard
  sudo chown -R navistfindadmin:navistfindadmin /var/www/navistfind-*
  ```

> ❗ Tip: If you prefer deploying as root, skip the `chown` change, but using a non-root user is safer.

✅ **Confirm Phase 1 complete** before continuing.

---

## Phase 2 – Deploy FastAPI Service (`navistfind-ai-service`)

Goal: Set up the SBERT FastAPI recommender on the VPS with systemd and Nginx.

Checklist:
1. Clone or upload the FastAPI project.
2. Configure Python virtual environment and dependencies.
3. Set environment variables.
4. Configure systemd.
5. Create Nginx reverse proxy with SSL.

### 1. Upload source code
- **Cursor step:** From `C:\CAPSTONE PROJECT\navistfind-ai-service`, push to Git or use `rsync/scp`.
- **Manual step:** If you zip and upload manually:
  ```powershell
  scp -r C:\CAPSTONE PROJECT\navistfind-ai-service navistfindadmin@srv1117384.hstgr.cloud:/var/www/
  ```

### 2. Create virtual environment
- **Cursor step (on VPS):**
  ```bash
  cd /var/www/navistfind-ai-service
  python3 -m venv venv
  source venv/bin/activate
  pip install --upgrade pip
  pip install -r requirements.txt
  ```
- If `requirements.txt` is missing, create it with dependencies such as:
  ```text
  fastapi
  uvicorn[standard]
  sentence-transformers
  numpy
  pydantic
  ```

### 3. Environment variables
- **Manual step:** Create a `.env` file if your FastAPI app expects one:
  ```bash
  nano /var/www/navistfind-ai-service/.env
  ```
  Example:
  ```
  OPENAI_API_KEY=your-key
  MODEL_PATH=app/models/navistfind-sbert
  ```
  - Keep secrets safe; avoid committing `.env`.

### 4. Systemd service
- **Cursor step:** Create `/etc/systemd/system/navistfind-ai.service`:
  ```
  [Unit]
  Description=NavistFind AI FastAPI Service
  After=network.target

  [Service]
  User=navistfindadmin
  WorkingDirectory=/var/www/navistfind-ai-service
  Environment="PATH=/var/www/navistfind-ai-service/venv/bin"
  ExecStart=/var/www/navistfind-ai-service/venv/bin/uvicorn app.main:app --host 127.0.0.1 --port 8001
  Restart=always

  [Install]
  WantedBy=multi-user.target
  ```
- **Manual step:** Reload systemd and start:
  ```bash
  sudo systemctl daemon-reload
  sudo systemctl enable navistfind-ai.service
  sudo systemctl start navistfind-ai.service
  sudo systemctl status navistfind-ai.service
  ```
- **Troubleshooting:** If you previously saw `ModuleNotFoundError: No module named 'app'`, verify the module path `app.main:app` matches your directory structure.

### 5. Nginx reverse proxy & SSL
- **Cursor step:** Create `/etc/nginx/sites-available/navistfind-ai`:
  ```
  server {
      listen 80;
      server_name api.navistfind.org;

      location / {
          proxy_pass http://127.0.0.1:8001;
          proxy_set_header Host $host;
          proxy_set_header X-Real-IP $remote_addr;
          proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
          proxy_set_header X-Forwarded-Proto $scheme;
      }
}
  ```
- **Cursor step:** Enable and test:
  ```bash
  sudo ln -s /etc/nginx/sites-available/navistfind-ai /etc/nginx/sites-enabled/
  sudo nginx -t
  sudo systemctl reload nginx
  ```
- **Manual step:** Issue SSL with Certbot:
  ```bash
  sudo certbot --nginx -d api.navistfind.org
  ```
- **Manual step:** Test HTTPS in a browser: `https://api.navistfind.org/docs`.

✅ **Confirm Phase 2 complete** before starting Phase 3.

---

## Phase 3 – Deploy Laravel Dashboard

Goal: Host the Laravel admin panel on the same VPS with PHP-FPM, MariaDB, and Nginx.

Checklist:
1. Create database and user.
2. Upload Laravel code.
3. Configure `.env`.
4. Install dependencies.
5. Configure Nginx and SSL.

### 1. Database
- **Manual step:** Log in to MariaDB:
  ```bash
  sudo mysql -u root -p
  ```
- **Manual step:** Run SQL commands:
  ```sql
  CREATE DATABASE navistfind_dashboard;
  CREATE USER 'navistfind_user'@'localhost' IDENTIFIED BY 'StrongPassword123!';
  GRANT ALL PRIVILEGES ON navistfind_dashboard.* TO 'navistfind_user'@'localhost';
  FLUSH PRIVILEGES;
  EXIT;
  ```
- **Tip:** Keep the password secure; you need it in Laravel `.env`.

### 2. Upload Laravel code
- **Manual/ Cursor:** Use Git, `scp`, or ask Cursor to clone into `/var/www/navistfind-dashboard`.
- Ensure `storage` and `bootstrap/cache` directories are writable:
  ```bash
  cd /var/www/navistfind-dashboard
  composer install --no-dev --optimize-autoloader
  php artisan key:generate
  ```

### 3. Configure environment
- **Manual step:** Create `.env` (copy from `.env.example`):
  ```
  APP_NAME=NavistFind
  APP_ENV=production
  APP_KEY=base64:generated-by-artisan
  APP_DEBUG=false
  APP_URL=https://dashboard.navistfind.org

  LOG_CHANNEL=stack
  LOG_DEPRECATIONS_CHANNEL=null
  LOG_LEVEL=warning

  DB_CONNECTION=mysql
  DB_HOST=127.0.0.1
  DB_PORT=3306
  DB_DATABASE=navistfind_dashboard
  DB_USERNAME=navistfind_user
  DB_PASSWORD=StrongPassword123!

  QUEUE_CONNECTION=database
  SESSION_DRIVER=file
  CACHE_DRIVER=file
  ```
- **Manual step:** Run migrations and seeders if available:
  ```bash
  php artisan migrate --force
  php artisan db:seed --force
  ```

### 4. File permissions
- **Cursor step:**
  ```bash
  sudo chown -R www-data:www-data /var/www/navistfind-dashboard/storage /var/www/navistfind-dashboard/bootstrap/cache
  sudo find /var/www/navistfind-dashboard -type f -exec chmod 644 {} \;
  sudo find /var/www/navistfind-dashboard -type d -exec chmod 755 {} \;
  ```

### 5. Nginx configuration
- **Cursor step:** Create `/etc/nginx/sites-available/navistfind-dashboard`:
  ```
  server {
      listen 80;
      server_name dashboard.navistfind.org;

      root /var/www/navistfind-dashboard/public;
      index index.php index.html;

      add_header X-Frame-Options "SAMEORIGIN";
      add_header X-Content-Type-Options "nosniff";

      location / {
          try_files $uri $uri/ /index.php?$query_string;
      }

      location ~ \.php$ {
          include snippets/fastcgi-php.conf;
          fastcgi_pass unix:/run/php/php8.2-fpm.sock;
      }

      location ~ /\.ht {
          deny all;
      }
}
  ```
- **Cursor step:** Enable site:
  ```bash
  sudo ln -s /etc/nginx/sites-available/navistfind-dashboard /etc/nginx/sites-enabled/
  sudo nginx -t
  sudo systemctl reload nginx
  ```
- **Manual step:** Issue certificate:
  ```bash
  sudo certbot --nginx -d dashboard.navistfind.org
  ```
- **Manual step:** Visit `https://dashboard.navistfind.org` to confirm the dashboard loads.

✅ **Confirm Phase 3 complete** before moving forward.

---

## Phase 4 – DNS Configuration

Goal: Point your domain records to the VPS so the public URLs work.

Checklist:
1. Configure Hostinger DNS (or Namecheap, whichever is authoritative).
2. Verify propagation.

- **Manual step:** In Namecheap, set nameservers to Hostinger’s (`ns1.dns-parking.com`, `ns2.dns-parking.com`). If already done, skip.
- **Manual step:** In Hostinger DNS Zone:
  | Host | Type | Value | TTL |
  |------|------|-------|-----|
  | @ | A | 72.61.116.160 | 600 |
  | www | CNAME | navistfind.org | 600 |
  | api | A | 72.61.116.160 | 600 |
  | dashboard | A | 72.61.116.160 | 600 |
- **Manual step:** Use `https://dnschecker.org` to confirm records propagate. Expect up to 30 minutes.
- **Troubleshooting:** If DNS takes too long, ensure there are no conflicting records on Namecheap.

✅ **Confirm Phase 4 complete** before Phase 5.

---

## Phase 5 – Connect Flutter App to Backend

Goal: Update the Flutter mobile app to use the new production API and dashboard URLs.

Checklist:
1. Configure environment constants.
2. Test API calls.

- **Manual step:** In Flutter project `C:\FINAL CAPSTONE PROJECT\navistfind`, create `lib/config/app_config.dart` (if you don’t already have an environment file):
  ```dart
  class AppConfig {
    static const String apiBaseUrl = 'https://api.navistfind.org';
    static const String dashboardUrl = 'https://dashboard.navistfind.org';
  }
  ```
- **Manual step:** Replace hard-coded localhost URLs with `AppConfig.apiBaseUrl`.
- **Manual step:** If you use `.env` or `flutter_config`, update the production file accordingly.
- **Manual step:** Run Flutter build targeting release to ensure there are no CORS or certificate issues:
  ```powershell
  flutter clean
  flutter pub get
  flutter run --release
  ```
- **Troubleshooting:** If API requests fail, enable CORS in FastAPI:
  ```python
  from fastapi.middleware.cors import CORSMiddleware

  app.add_middleware(
      CORSMiddleware,
      allow_origins=["https://navistfind.org", "https://dashboard.navistfind.org"],
      allow_credentials=True,
      allow_methods=["*"],
      allow_headers=["*"],
  )
  ```

✅ **Confirm Phase 5 complete** before proceeding.

---

## Phase 6 – Security Hardening

Goal: Lock down the VPS to reduce risk.

- **Cursor step:** Configure UFW firewall:
  ```bash
  sudo ufw allow OpenSSH
  sudo ufw allow 'Nginx Full'
  sudo ufw enable
  sudo ufw status
  ```
- **Manual step:** Install Fail2Ban defaults (already installed). To protect SSH, tweak `/etc/fail2ban/jail.local`:
  ```
  [sshd]
  enabled = true
  port = ssh
  filter = sshd
  logpath = /var/log/auth.log
  maxretry = 5
  bantime = 3600
  ```
  Restart:
  ```bash
  sudo systemctl restart fail2ban
  sudo fail2ban-client status sshd
  ```
- **Manual step:** Disable password SSH login if keys work:
  ```bash
  sudo nano /etc/ssh/sshd_config
  ```
  Set:
  ```
  PasswordAuthentication no
  PermitRootLogin prohibit-password
  ```
  Reload SSH: `sudo systemctl reload sshd`.

✅ **Confirm Phase 6 complete**.

---

## Phase 7 – Monitoring, Backups, and Maintenance

Goal: Set up daily habits so the system stays healthy.

- **Manual step:** In Hostinger VPS panel, enable automatic backups if available (or schedule snapshots).
- **Manual step:** Create cron jobs on the server:
  ```bash
  sudo crontab -e
  ```
  Example entries:
  ```
  0 2 * * * /usr/bin/certbot renew --quiet
  0 3 * * 0 /usr/bin/apt update && /usr/bin/apt upgrade -y && /usr/bin/systemctl restart navistfind-ai.service
  ```
- **Manual step:** Configure Laravel scheduled tasks:
  ```bash
  * * * * * www-data php /var/www/navistfind-dashboard/artisan schedule:run >> /dev/null 2>&1
  ```
- **Manual step:** Monitor resource usage from Hostinger hPanel or CLI:
  ```bash
  htop
  df -h
  journalctl -u navistfind-ai.service -f
  ```
- **Cursor step:** Automate logs retrieval or updates by asking Cursor to fetch system logs when needed.
- **Tip:** Keep `.env` backups on your local machine (encrypted). Rotate API keys periodically.

✅ **Confirm Phase 7 complete**.

---

## Phase 8 – Final Verification

- **Manual step:** Test the full flow:
  1. Upload a lost item through the mobile app.
  2. Check the dashboard to confirm data appears.
  3. Verify AI recommendations via the FastAPI endpoint.
- **Manual step:** Share the app with a friend or mentor to perform acceptance testing.
- **Cursor step:** If bugs appear, ask Cursor to review logs or patch code.

Congratulations! You now have a production-ready deployment on Hostinger. Keep this guide for future updates, and feel free to refine any phase as the project evolves.

