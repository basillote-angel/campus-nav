# Render Deployment Checklist (5-Month Hosting)

## 1. Sign Up & Prepare Accounts
1. Create a Render account at <https://render.com/signup> (GitHub/GitLab/Email login).
2. Purchase a custom domain from Namecheap or Cloudflare (optional but recommended).
3. Set up storage (Backblaze B2, AWS S3, or DigitalOcean Spaces) with read/write keys.
4. Ensure code repositories for:
   - `campus-nav` (Laravel web dashboard)
   - `navistfind-ai-service` (FastAPI SBERT service)

## 2. Pre-Deployment Prep
1. Clean and push latest code to default branch.
2. Make sure `.env.example` includes all required variables.
3. Build Flutter app (only needs API base URLs later).
4. Create database dump for initial data (optional seed).
5. Draft environment variables (DB credentials, API keys, storage params).

## 3. Deploy Laravel Dashboard (`campus-nav`)
1. In Render, click **+ New → Web Service**.
2. Connect Git repo and branch; name it `campus-nav`.
3. Select **Starter** ($7/mo) or **Standard** ($25/mo if heavier workloads).
4. Set runtime:
   - Build Command: `composer install --no-dev --optimize-autoloader`
   - Start Command: `php artisan serve --host 0.0.0.0 --port 10000`
5. Configure environment variables (from `.env.example`).
6. Add secrets for storage (S3/B2 keys) and service API URLs.
7. Deploy; confirm health check succeeds.

## 4. Provision Database
1. In Render, **+ New → PostgreSQL** (or MySQL).
2. Choose **Starter** ($7/mo) or `Standard Plus` ($15/mo) if more performance.
3. Copy connection string; store in password manager.
4. Update Laravel service env vars (`DB_CONNECTION`, `DB_HOST`, etc.).
5. Redeploy Laravel; run migrations/seed via Render Shell or deploy hook.

## 5. Deploy FastAPI SBERT Service
1. In Render, **+ New → Web Service**; choose `navistfind-ai-service` repo.
2. Plan: Start with **Starter** ($7/mo). Upgrade to Standard (1 CPU/2 GB, $25/mo) if SBERT needs more RAM.
3. Build Command (if needed): `pip install -r requirements.txt`.
4. Start Command: `uvicorn main:app --host 0.0.0.0 --port 10000`.
5. Configure environment variables (model paths, DB connection, API keys).
6. Deploy; ensure it loads SBERT without memory errors.

## 6. Connect Services & Storage
1. Update Laravel `.env` with FastAPI base URL.
2. Add API authentication (shared secret or API key) and set in both services.
3. Configure Laravel storage driver for S3-compatible bucket.
4. Test file upload path; ensure bucket has proper permissions.

## 7. Domain & SSL
1. In Render service settings, add custom domain.
2. Update domain DNS (CNAME for web services).
3. Render auto-provisions SSL certificates (Let’s Encrypt).
4. Verify HTTPS endpoints; update Flutter app API base URL.

## 8. CI/CD & Automation
1. Enable auto-deploy on `main` branch or set manual approval.
2. Add staging environment (optional) for testing.
3. Configure cron jobs or background workers (e.g., Laravel queue worker).
4. Document rollback steps (Redeploy previous commit).

## 9. Monitoring & Backups
1. Watch Render logs/metrics; set alerts for downtime/error rate.
2. Enable database automated backups (daily). Export snapshots quarterly.
3. Monitor storage usage; lifecycle policies on bucket to delete stale files.
4. Schedule monthly review of costs and performance.

## 10. Flutter Mobile Integration
1. Point app config to new API URLs (Laravel + FastAPI).
2. Run regression tests on key flows (login, search, item recommendation).
3. Rebuild APK/AAB and distribute (Play Store or direct).
4. Monitor crash/error logs (Firebase Crashlytics or similar).

## 11. Shutdown Plan (After 5 Months)
1. Notify users and stakeholders of end date.
2. Export final database dumps and bucket contents.
3. Pause or delete Render services to stop billing.
4. Archive source code and deployment notes.
5. Cancel domain renewal if not needed.

## 12. Documentation & Access Control
1. Document environment variables, secrets, and deployment steps.
2. Limit Render/team access to minimum required roles.
3. Store credentials in a password manager.
4. Keep incident response contacts up to date.