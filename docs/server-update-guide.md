## CampusNav Production Update Guide

Use this runbook whenever you need to deploy the latest `main` branch to the
existing Hostinger VPS (`srv1117384` – `72.61.116.160`). Adjust paths or service
names if your server differs.

---

### 1. Prerequisites
- SSH access as `root` (or a sudo-capable deploy user).
- Git installed on the server and remote `origin` pointing to
  `git@github.com:basillote-angel/campus-nav.git`.
- Laravel project checked out at `/var/www/navistfind-dashboard` (adjust if your
  installation lives elsewhere).
- Backups:
  - `mysqldump` export from the MariaDB instance running on the VPS
    (`mysql --version` shows 10.11+).
  - Recent copy of `.env`.

---

### 2. Connect and Prepare
```bash
ssh root@72.61.116.160
cd /var/www/navistfind-dashboard
php artisan down  # optional, only if you expect noticeable downtime
```

> **Note:** Ensure the VPS has GitHub SSH access. If `git fetch` fails with
> “Permission denied (publickey)” run:
> ```bash
> mkdir -p /root/.ssh
> ssh-keygen -t ed25519 -C "srv1117384 deploy"   # accept defaults, no passphrase
> cat /root/.ssh/id_ed25519.pub   # add this value as a deploy key in GitHub
> eval "$(ssh-agent -s)"
> ssh-add /root/.ssh/id_ed25519
> ```

---

### 3. Update Application Code
```bash
cd /var/www/navistfind-dashboard
git fetch origin main
git checkout main
git reset --hard origin/main
```

---

### 4. Install Backend & Frontend Dependencies
```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build      # skip if assets are built elsewhere
```

---

### 5. Refresh Environment Configuration
1. Open `.env` and replace values as needed. Current production values:
   ```
   APP_NAME=Laravel
   APP_ENV=production
   APP_KEY=base64:TiuUjRvek4sg5Zrp/ndDWwjYcfqaueNAoKLdKEmvqOY=
   APP_DEBUG=false
   APP_URL=//navistfind.org
   ...
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=navistfind_db
   DB_USERNAME=navistfind_db_user
   DB_PASSWORD=GMi4U3X3vao3qqHpUy6rlSatvXyrkbpj
   ...
   MAIL_HOST=smtp.yourprovider.com
   MAIL_USERNAME=no-reply@navistfind.org
   MAIL_PASSWORD=Navistfind888.
   ...
   AWS_ACCESS_KEY_ID=00555168d4354db0000000001
   AWS_SECRET_ACCESS_KEY=K005zzBcjQlVMEnFXJo5WoKZotyhDHK
   AWS_BUCKET=navistfind-storage
   AWS_ENDPOINT=https://s3.us-east-1.backblazeb2.com
   ...
   JWT_SECRET=L1CwIcuQpi3nU1Z5d1HeK5M3OsKEtCjDlhWKgDYchStE9yhdRuixtvDJXKnHuaZD
   ```
2. Keep only **one** `QUEUE_CONNECTION` line—set it to `database` for async
   processing or `sync` for debugging.
3. Save the file and ensure ownership/permissions remain unchanged.

---

### 6. Database & Cache Tasks
```bash
php artisan migrate --force
php artisan cache:clear
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart   # if workers are supervised
```

If you run workers under Supervisor, also run
`supervisorctl reload` (or restart the specific program) to pick up the new
code.

---

### 7. Bring the Site Back
```bash
php artisan up   # only if you previously ran 'artisan down'
```

---

### 8. Verification Checklist
- Visit `https://navistfind.org` and log in as an admin; confirm dashboard
  widgets, claims list, and AI recommendations load.
- Upload a test found item and ensure notifications (email + in-app) fire.
- Run the Flutter client (if applicable) to confirm push navigation still
  works with the updated API.
- Tail logs while testing:
  ```bash
  tail -f storage/logs/laravel.log
  ```
- Monitor server health (`htop`, disk usage, queue table).

---

### 9. Rollback Plan
1. `git reset --hard <previous-good-commit>` or `git checkout tags/<release>`.
2. Restore the previous `.env` if it changed.
3. If migrations were destructive, restore the DB backup taken in step 1.
4. Rerun caches and restart services.

---

### 10. Hardening Tips
- Rotate the credentials in `.env` after major deployments and store them in a
  secure secrets manager.
- Create a non-root deploy user and disable password SSH once key-based access
  is set.
- Schedule nightly DB backups and copy them off the VPS.
*** End Patch

