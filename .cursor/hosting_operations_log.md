## Hosting Operations Log

**HTTPS redirect** – Visit:
   - `http://navistfind.org` and `http://www.navistfind.org` (they should 301 to `https://dashboard.navistfind.org`).
   - `https://dashboard.navistfind.org` (Laravel dashboard)
   - `https://api.navistfind.org/docs` (FastAPI Swagger)

This log captures the key actions completed while deploying NavistFind to the Hostinger VPS (`srv1117384.hstgr.cloud`, IP `72.61.116.160`). Use it as a quick reference for future maintenance or troubleshooting.

---

### Phase 1 – VPS Preparation
- Updated packages and installed base tooling (`software-properties-common`, `ufw`, `fail2ban`, `unzip`).
- Installed runtimes: Python 3, Node.js 20, PHP 8.2 (FPM + required extensions), MariaDB, Nginx, Certbot.
- Secured MariaDB via `mysql_secure_installation`.
- Created deployment directories under `/var/www/` for FastAPI (`navistfind-ai-service`) and Laravel (`navistfind-dashboard`).

### Phase 2 – FastAPI Service (`navistfind-ai-service`)
- Cloned repo: `git clone https://github.com/basillote-angel/navistfind-ai-service.git /var/www/navistfind-ai-service`.
- Created Python virtual environment, installed dependencies from `requirements.txt`.
- Configured `.env` with:
  ```
  MODEL_DIR=/var/www/navistfind-ai-service/models/sbert_lost_found_model
  AI_SERVICE_API_KEY=<secret>
  HOST=127.0.0.1
  PORT=8001
  ```
- Installed systemd unit `navistfind-ai.service` (ExecStart `uvicorn main:app --port 8001`) and enabled it.
- Configured Nginx reverse proxy (`/etc/nginx/sites-available/navistfind-ai`) → `127.0.0.1:8001`.
- Issued SSL via Certbot (`sudo certbot --nginx -d api.navistfind.org`) and verified `https://api.navistfind.org/docs`.

### Phase 3 – Laravel Dashboard (`navistfind-dashboard`)
- Cloned repo: `git clone https://github.com/basillote-angel/campus-nav.git /var/www/navistfind-dashboard`.
- Installed Composer dependencies (`composer install --no-dev --optimize-autoloader`).
- Built frontend assets (`npm install && npm run build`) to generate `public/build/manifest.json`.
- Configured `.env` with production DB/app settings and generated `APP_KEY`.
- MariaDB:
  - Database `navistfind_dashboard`.
  - User `navistfind_user` (host `localhost`, password stored securely).
  - Ran `php artisan migrate --force`.
  - Seeded QA demo data (`php artisan db:seed --class=Database\\Seeders\\QaDemoSeeder --force`).
  - Added missing `collection_deadline`, `collected_at`, `collected_by` columns + indexes/FK in `found_items`.
- File permissions: `www-data` owns `storage` and `bootstrap/cache`.
- Nginx site (`/etc/nginx/sites-available/navistfind-dashboard`) -> PHP-FPM socket `/run/php/php8.2-fpm.sock`.
- Issued SSL via Certbot (`sudo certbot --nginx -d dashboard.navistfind.org`).
- Cleared Laravel caches (`php artisan optimize:clear`).

### Phase 4 – DNS + Redirects
- Hostinger DNS zone records now point to the VPS:
  - `@` A → `72.61.116.160`
  - `www` CNAME → `navistfind.org`
  - `api` A → `72.61.116.160`
  - `dashboard` A → `72.61.116.160`
- Added Nginx redirect for root domain (`navistfind.org`, `www.navistfind.org`) to `https://dashboard.navistfind.org`.
- Issued SSL for root domain via `sudo certbot --nginx -d navistfind.org -d www.navistfind.org`.

---

### Current Status (2025-11-11)
- **FastAPI**: `navistfind-ai.service` active, accessible via `https://api.navistfind.org`.
- **Laravel Dashboard**: Live at `https://dashboard.navistfind.org`, migrations/seeds applied, Vite assets built.
- **Admin Login**: Demo account `admin@navistfind.com / password`; change password post-login.
- **HTTPS**: Certbot managed certificates for `api`, `dashboard`, and root domain; auto-renew cron handled by Certbot.
- **DNS**: Records managed in Hostinger hPanel → Domains → `navistfind.org` → DNS Zone.

---

### Next Steps & Maintenance Tips
- For new code deployments:
  ```
  cd /var/www/navistfind-ai-service && git pull && source venv/bin/activate && pip install -r requirements.txt && sudo systemctl restart navistfind-ai.service
  cd /var/www/navistfind-dashboard && git pull && composer install --no-dev --optimize-autoloader && npm run build && php artisan migrate --force && php artisan optimize:clear
  ```
- Check service health:
  - `sudo systemctl status navistfind-ai.service`
  - `sudo tail -f /var/log/nginx/navistfind-ai-error.log`
  - `sudo tail -f /var/www/navistfind-dashboard/storage/logs/laravel.log`
- Database backups: enable or schedule via Hostinger VPS panel (snapshots) or use `mysqldump`.
- Security: keep `ufw` enabled (`OpenSSH`, `Nginx Full`), rotate passwords/keys, revoke unused tokens.
- Document any future schema changes in this log to maintain parity between repo migrations and production fixes.















