## Deployment Environment Snapshot

- VPS plan: Hostinger KVM1, Ubuntu 24.04 LTS (`srv1117384.hstgr.cloud`), IPv4 `72.61.116.160`.
- SSH access: root user + key `MyLaptopKey`; non-root sudo user `navistfindadmin`.
- Domain `navistfind.org` uses Hostinger nameservers (`ns1.dns-parking.com`, `ns2.dns-parking.com`) managed through Hostinger DNS Manager.
- Current DNS records:
  - `@` A record → `72.61.116.160` (VPS).
  - `www` CNAME → `navistfind.org`.
  - `api` A record → `72.61.116.160` (VPS, FastAPI endpoint).
- FastAPI service deployed on VPS under `/var/www/navistfind-ai-service`, managed by systemd (`navistfind-ai.service`) and proxied via Nginx; SSL issued by Certbot for `api.navistfind.org`.
- FastAPI still needs corrected `ExecStart` module path (error: `ModuleNotFoundError: No module named 'app'`).
- Laravel admin not yet deployed; no shared hosting plan active. Decision needed:
  1. Purchase Hostinger shared hosting for Laravel, or
  2. Install PHP, MariaDB/MySQL, and deploy Laravel on the existing VPS.



