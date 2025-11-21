## 1. Mobile App (Flutter)

### Login/Register
- ✅ Current Functionality: Handles email/password registration and login, storing role-scoped tokens; performs baseline credential validation; retrieves user profile after authentication.
- ⚙️ Needs Revision / Missing Features: Harden validation (password strength rules, duplicate email check, captcha/lockout on repeated failures); add password reset (request + confirm) and email verification flows; implement token refresh with idle timeout enforcement; ensure profile sync immediately after login for downstream personalization.
- 🔗 Backend/API Connection: `POST /api/auth/register`, `POST /api/auth/login`, `GET /api/user` for profile sync; extend with `POST /api/auth/password/forgot`, `POST /api/auth/password/reset`, and `POST /api/auth/email/verify` endpoints; requires refresh-token support for session renewal.
- 🔔 Notification Triggers: Send welcome notification on successful verification; alert admins when manual approval required; push password-reset confirmations and security alerts for suspicious login attempts.
- 🧠 AI or Smart Logic: Use profile sync to preload user preferences that influence recommendation weighting; flag unusual login behavior for analytics.

### Home Dashboard
- ✅ Current Functionality: Presents shortcuts for posting lost items, browsing found items, viewing AI matches, and accessing AR navigation; surfaces counts for open claims, unresolved notifications, and recent activity.
- ⚙️ Needs Revision / Missing Features: Add live status badges for key entities (open/matched/returned); embed analytics snapshot (match success rate, average claim turnaround); provide contextual empty-state guidance and quick tips; support manual refresh with optimistic UI.
- 🔗 Backend/API Connection: `GET /api/dashboard/summary` for counts and shortcuts, `GET /api/notifications/unread-count` for badges, `GET /api/items/stats` for analytics; consider websocket or SSE channel for real-time updates.
- 🔔 Notification Triggers: Pull-to-refresh and background sync should reconcile unread notifications; display inline alert cards when admin approvals/rejections or pickup reminders arrive.
- 🧠 AI or Smart Logic: Highlight top recommendation tile sourced from latest match batch including confidence score; tailor quick actions based on user history (e.g., prompt to complete pending claim forms).

### Lost Item Posting
- ✅ Current Functionality: Collects title, description, category, location lost, and date lost without photo support; submits lost item record with status `open`.
- ⚙️ Needs Revision / Missing Features: Add photo capture/upload flow (including storage and compression); add form autosave; validate location against campus map; capture optional tags for AI enrichment.
- 🔗 Backend/API Connection: `POST /api/lost-items` (currently JSON payload, extend to multipart when photos added); `GET /api/categories`; `GET /api/locations`.
- 🔔 Notification Triggers: After submission, notify user that AI matching is pending; optionally inform admin of new lost item for manual review.
- 🧠 AI or Smart Logic: Immediately queue ComputeItemMatches job via Laravel; store embeddings metadata for future recalculations.

### Found Item Listing
- ✅ Current Functionality: Provides a paginated catalog of found items with basic filters (category, location, date) and detail screens showing item metadata and claim status.
- ⚙️ Needs Revision / Missing Features: Add advanced filters (AI match score ranges, submission timeframe, storage location); visually distinguish items under review or returned; enable saved filter sets; ensure list refreshes after claim decisions to avoid stale caches.
- 🔗 Backend/API Connection: `GET /api/found-items` supporting filter/query params, `GET /api/found-items/{id}` for detail; require cache-busting headers or ETags after updates.
- 🔔 Notification Triggers: Browsing itself triggers none, but opening detail should log a view analytics event and clear any related match notification.
- 🧠 AI or Smart Logic: When navigated from AI recommendations, show source match score and confidence context; suggest similar items if viewed item already claimed.

### AI Recommendations
- ✅ Current Functionality: Lists AI-curated found items ordered by similarity score above global threshold; supports navigation to item detail and immediate claim initiation.
- ⚙️ Needs Revision / Missing Features: Provide score explanation tooltip and match context (shared attributes); supply guidance when list empty or stale; enable dismissal or pinning of recommendations; capture user feedback (confirm/reject) for model tuning and prevent resurfacing dismissed pairs.
- 🔗 Backend/API Connection: `GET /api/items/recommended` with pagination and score filters; `POST /api/matches/{id}/feedback` (confirm/false-positive); consider `DELETE /api/matches/{id}` for dismissal.
- 🔔 Notification Triggers: Handles push notifications for new matches, clears associated badges upon view, and optionally schedules reminder if user ignores high-confidence match.
- 🧠 AI or Smart Logic: Utilizes SBERT cosine similarity with category-aware thresholds; logs feedback to adjust weighting; may downgrade recommendations when multiple dismissals occur for same item pair.

### Claim Item Flow
- ✅ Current Functionality: Claim form requires message and optional contact info; submits claim, transitions found item status to `matched`, stores claim metadata.
- ⚙️ Needs Revision / Missing Features: Add validation hints; prevent duplicate claims by same user; allow attachment upload (proof of ownership); support editing claim before admin action.
- 🔗 Backend/API Connection: `POST /api/items/{id}/claim`, `GET /api/claims/user`, `DELETE /api/claims/{id}` for withdrawal.
- 🔔 Notification Triggers: Show confirmation notification; listen for admin approval/rejection (FCM topics per claim).
- 🧠 AI or Smart Logic: Use similarity confidence and claim message analysis to flag low-confidence matches for admin review.

### Notifications
- ✅ Current Functionality: Lists in-app notifications with read/unread states; includes push handling via FCM; routes to relevant screens.
- ⚙️ Needs Revision / Missing Features: Implement grouped notifications (claims, matches, reminders); add snooze and bulk mark-as-read; ensure offline caching.
- 🔗 Backend/API Connection: `GET /api/notifications`, `PATCH /api/notifications/{id}/read`, `PATCH /api/notifications/read-all`.
- 🔔 Notification Triggers: Receives claim status updates, match alerts, collection reminders, AR guidance prompts.
- 🧠 AI or Smart Logic: Prioritize notifications using urgency scoring (claims awaiting response, deadlines).

### Profile & Settings
- ✅ Current Functionality: Displays user info, allows editing contact details, toggling notification preferences, and logging out.
- ⚙️ Needs Revision / Missing Features: Add password change, device management, two-factor toggle, export of personal data; integrate privacy consent logs.
- 🔗 Backend/API Connection: `GET /api/profile`, `PUT /api/profile`, `POST /api/auth/logout`, `GET /api/settings/preferences`, `PUT /api/settings/preferences`.
- 🔔 Notification Triggers: Notify user when credentials or security settings change; push device re-auth prompts.
- 🧠 AI or Smart Logic: Capture user category preferences to refine recommendation weighting.

### AR Navigation
- ✅ Current Functionality: Displays AR markers aligned with campus map; allows selection of campus buildings and path guidance; uses device sensors for orientation.
- ⚙️ Needs Revision / Missing Features: Sync location data with lost/found item locations; integrate accessibility routes; cache map assets; support offline fallback instructions.
- 🔗 Backend/API Connection: `GET /api/ar/locations`, `GET /api/ar/paths`, `POST /api/ar/feedback`.
- 🔔 Notification Triggers: Send reminders for scheduled campus tours or collection directions.
- 🧠 AI or Smart Logic: Use context-aware routing (time of day, building hours); recommend nearest admin office for item pickup.

## 2. Web Admin (Laravel Dashboard)

### Dashboard Overview
- ✅ Current Role: Presents summary cards for pending claims, approved/rejected counts, unclaimed items, and overall system metrics.
- ⚙️ Functions to Add/Fix: Implement real-time widgets; add alerts for items pending >24 hours; include recent activity timeline.
- 🔗 Data/API Dependencies: `GET /admin/api/dashboard/summary`, analytics aggregation jobs, notification counts.
- 🧾 Admin Actions: Quick links to approve oldest claims, mark items as collected, open analytics reports.
- 🧩 Auto-updates or Triggers: Auto-refresh dashboard via websockets or polling every 60 seconds.

### Found Item Management
- ✅ Current Role: Lists found items with filters; supports create/edit/delete; shows claim status.
- ⚙️ Functions to Add/Fix: Enforce photo verification; add bulk status updates; attach collection instructions; integrate conflict indicators.
- 🔗 Data/API Dependencies: `GET /admin/api/found-items`, `POST /admin/api/found-items`, `PUT /admin/api/found-items/{id}`, `DELETE /admin/api/found-items/{id}`.
- 🧾 Admin Actions: Approve item publication, archive collected items, mark physical storage location.
- 🧩 Auto-updates or Triggers: Notify users when new found item matches their lost item; re-run AI matching after edits.

### Lost Item Management
- ✅ Current Role: Displays lost item submissions for moderation; allows editing or closing.
- ⚙️ Functions to Add/Fix: Add verification workflow before publishing; allow admins to link lost and found items manually; track resolution notes.
- 🔗 Data/API Dependencies: `GET /admin/api/lost-items`, `PUT /admin/api/lost-items/{id}`, `PATCH /admin/api/lost-items/{id}/status`.
- 🧾 Admin Actions: Close lost item after approval, merge duplicates, attach admin comments.
- 🧩 Auto-updates or Triggers: Auto-close related lost item when claim approved; notify user when admin intervenes.

### Claim Requests
- ✅ Current Role: Central queue showing claims with claimant info, message, match score, and timestamps.
- ⚙️ Functions to Add/Fix: Add conflict resolution UI; enforce mandatory rejection reason; allow scheduling pickup and logging collection.
- 🔗 Data/API Dependencies: `GET /admin/api/claims?status=pending`, `POST /admin/api/claims/{id}/approve`, `POST /admin/api/claims/{id}/reject`, `POST /admin/api/claims/{id}/collection`.
- 🧾 Admin Actions: Approve, reject, request more info, assign to staff, mark collected.
- 🧩 Auto-updates or Triggers: Approval triggers push/email to claimant, closes lost item, marks match as confirmed; rejection clears claim metadata and reopens found item.

### Notifications
- ✅ Current Role: Displays system-generated alerts for admins (new claims, overdue items, AI failures).
- ⚙️ Functions to Add/Fix: Implement role-based filters; enable acknowledgement tracking; escalate unresolved alerts.
- 🔗 Data/API Dependencies: `GET /admin/api/notifications`, `PATCH /admin/api/notifications/{id}/ack`.
- 🧾 Admin Actions: Mark as read, assign notification to team member, export log.
- 🧩 Auto-updates or Triggers: Sync with FCM topics for admins; push high-priority alerts via email/SMS.

### AI Matches Monitoring
- ✅ Current Role: Shows AI match queue with scores and statuses.
- ⚙️ Functions to Add/Fix: Provide manual override to confirm/deny matches; display embedding timestamps; highlight items needing recompute.
- 🔗 Data/API Dependencies: `GET /admin/api/matches`, `POST /admin/api/matches/{id}/recompute`.
- 🧾 Admin Actions: Flag false positives, trigger recompute, adjust thresholds per category.
- 🧩 Auto-updates or Triggers: When admin updates threshold, reprocess queue; log interventions for model tuning.

### Analytics/Reports
- ✅ Current Role: Offers charts for claims processed, approval rates, match accuracy.
- ⚙️ Functions to Add/Fix: Add date filters, export to CSV/PDF, track pickup compliance, include AR navigation usage.
- 🔗 Data/API Dependencies: Aggregated data from analytics service, scheduled jobs, `GET /admin/api/analytics`.
- 🧾 Admin Actions: Schedule automated reports, share report snapshots.
- 🧩 Auto-updates or Triggers: Refresh charts after daily batch jobs; send weekly summary to stakeholders.

### Activity Logs
- ✅ Current Role: Records admin actions (login, approvals, edits).
- ⚙️ Functions to Add/Fix: Implement tamper-proof storage; add search/filter; link log entries to entity detail pages.
- 🔗 Data/API Dependencies: `GET /admin/api/activity-logs`, audit middleware.
- 🧾 Admin Actions: Review suspicious activity, export logs for compliance.
- 🧩 Auto-updates or Triggers: Auto-create log entries on every CRUD action; alert security when anomalies detected.

### Admin Settings
- ✅ Current Role: Manage admin accounts, roles, thresholds, notification preferences.
- ⚙️ Functions to Add/Fix: Add role-based permissions matrix; support environment-specific configuration; enable audit of changes.
- 🔗 Data/API Dependencies: `GET /admin/api/settings`, `PUT /admin/api/settings`, `POST /admin/api/admin-users`, `PUT /admin/api/admin-users/{id}`.
- 🧾 Admin Actions: Update AI thresholds, manage FCM keys, configure collection deadlines.
- 🧩 Auto-updates or Triggers: Persist changes to config cache; trigger re-sync to mobile clients where applicable.

## 3. AI Service (Python / SBERT)

### Item Text Embedding
- ✅ Current Functionality: Uses SBERT to generate embeddings for lost and found item descriptions upon creation or update.
- ⚙️ Needs Improvement / Edge Cases: Ensure multilingual support; handle missing or short descriptions by augmenting with metadata; cache embeddings with versioning.
- 🔗 Integration Points: Laravel webhooks post new/updated items to FastAPI endpoint `/embeddings/generate`; Flutter displays score metadata derived from embeddings.

### Similarity Computation
- ✅ Current Functionality: Calculates cosine similarity between lost and found embeddings; outputs match scores.
- ⚙️ Needs Improvement / Edge Cases: Introduce category-specific thresholds; adjust for time decay; filter matches involving already returned items.
- 🔗 Integration Points: Results pushed back to Laravel via `/matches/update`; Flutter fetches through `GET /api/items/recommended`.

### Matching Threshold Logic
- ✅ Current Functionality: Applies global similarity threshold (e.g., >0.60) to determine candidate matches.
- ⚙️ Needs Improvement / Edge Cases: Allow admin-configurable thresholds per category; integrate dynamic adjustments based on feedback; prevent repetitive notifications for same pair.
- 🔗 Integration Points: Threshold values managed in Laravel Admin Settings; AI service pulls config via secure endpoint `/config/thresholds`.

### Job Queue / Background Task Trigger
- ✅ Current Functionality: Laravel queues ComputeItemMatches job after item creation; job requests AI service for embeddings and matches.
- ⚙️ Needs Improvement / Edge Cases: Add retry with exponential backoff; ensure idempotency; implement job deduplication when multiple updates occur rapidly.
- 🔗 Integration Points: Laravel queue workers, Redis broker, FastAPI background tasks; status callback endpoint `/jobs/status`.

### API Endpoints
- ✅ Current Functionality: Exposes REST endpoints for embedding generation, match computation, health checks.
- ⚙️ Needs Improvement / Edge Cases: Add authentication (API keys/JWT); rate-limit requests; provide batch endpoints for nightly recompute.
- 🔗 Integration Points: Consumed by Laravel services and admin dashboard health monitor.

### Logging & Error Handling
- ✅ Current Functionality: Logs errors to local storage; sends basic metrics to monitoring service.
- ⚙️ Needs Improvement / Edge Cases: Centralize logging with correlation IDs; add alerting on match pipeline failures; implement circuit breaker when AI service unavailable.
- 🔗 Integration Points: Laravel should ingest error webhooks; admin notifications display AI outage alerts.

## 4. System-Wide Functional Requirements

- Push Notifications (FCM): Ensure consistent token management; map events (new match, claim status, pickup reminders) to appropriate channels for both users and admins.
- Claim Approval/Rejection Notifications: Automate messaging with contextual details (item name, reason, collection instructions); synchronize state across mobile and web clients.
- Automatic Status Updates: Approving claim sets found item to `returned`, closes linked lost item, marks match as `confirmed`; rejection reverts to `unclaimed` and clears claim metadata.
- Analytics & Metrics: Track claim lifecycle durations, match success rate, collection compliance, AR navigation usage; expose dashboards and exports.
- AR Navigation Logic: Maintain mapping between campus POIs and lost/found item locations; allow admin updates; sync to mobile AR module.
- Multi-Claim Conflict Handling: Flag items with multiple claims, require admin comparison workflow, notify all claimants of final decision.
- Physical Collection Tracking: Record scheduled pickup, collection deadline, actual pickup timestamp, admin verifier, and optional signature.
- Deadlines and Reminders: Generate automatic reminders 3 days and 1 day before collection deadline; escalate overdue items to admin queue.
- Logging and Security Rules: Enforce audit logging for all CRUD and approval actions; implement RBAC; monitor suspicious activity with alerts.

## 5. Enhancement Summary

- 🔧 Top Priority Fixes: Implement claim conflict resolution and physical collection tracking; add mandatory rejection reasons; secure AI service with authentication and retries.
- 📦 New Features to Implement: Password reset and email verification; pickup scheduling with reminders; user feedback loop for AI matches.
- 🧠 AI Optimization Tasks: Tune category-specific thresholds; incorporate feedback-based retraining; handle multilingual descriptions.
- 📊 Analytics Improvements: Expand dashboards to include pickup compliance and AR usage; enable scheduled report exports; visualize claim turnaround times.
- ⚙️ Backend-Frontend Sync Enhancements: Ensure status updates propagate in real time via websockets/FCM; synchronize thresholds and settings across services; improve cache invalidation for item listings.

