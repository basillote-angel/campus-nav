### NavistFind Server Environment Setup

#### Required variables (.env)
APP_NAME=NavistFind
APP_ENV=production
APP_KEY=base64:...
APP_URL=https://api.yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=navistfind
DB_USERNAME=navistfind
DB_PASSWORD=your_db_password

# Sanctum / CORS (adjust to your mobile/admin domains)
SANCTUM_STATEFUL_DOMAINS=yourdomain.com,api.yourdomain.com,admin.yourdomain.com
SESSION_DOMAIN=.yourdomain.com

# AI Service (SBERT FastAPI)
AI_SERVICE_URL=https://ai.yourdomain.com
AI_SERVICE_API_KEY=optional_if_used

# AI Recommender knobs
AI_BASE_URL=${AI_SERVICE_URL}
AI_TOP_K=10
AI_THRESHOLD=0.60

# Optional mail/logging/queue
LOG_CHANNEL=stack
QUEUE_CONNECTION=database
MAIL_MAILER=log

# FCM (Firebase Cloud Messaging)
FCM_SERVER_KEY=

#### Database indexes and search
- Items table includes indexes on: type, category, status, lost_found_date, created_at
- FULLTEXT on: name, description (MySQL InnoDB)

#### API highlights
- Auth: Sanctum token; Bearer auth on mobile
- Items:
  - GET /api/items?type=&category=&query=&dateFrom=&dateTo=&sort=newest|relevance
  - POST /api/items (role rules enforced; students=lost only)
  - GET /api/items/{id}
  - PUT /api/items/{id}
  - DELETE /api/items/{id}
  - GET /api/items/{id}/matches
  - GET /api/items/recommended
  - POST /api/items/{id}/claim (student)
  - POST /api/items/{id}/approve-claim (admin/staff)
  - POST /api/items/{id}/reject-claim (admin/staff)
  - POST /api/ai/feedback (best-effort logging)
- Optional matches on create/update:
  - include_matches=1 → response { item, matches: [...] }

#### Admin web additions
- Matches Queue: GET /admin/matches (filters: ?days=14&minScore=0.6)
- Claims Review: GET /admin/claims (tabs: pending/approved/rejected)
  - POST /admin/claims/{id}/approve
  - POST /admin/claims/{id}/reject (reason)

#### Deployment notes (VPS)
- Nginx → PHP-FPM (Laravel) at api.yourdomain.com
- Python + Uvicorn (FastAPI) at ai.yourdomain.com
- MySQL 8, Certbot for HTTPS
- Systemd services: php-fpm, queue worker (optional), uvicorn


