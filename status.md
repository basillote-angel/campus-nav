## LostFoundFlowImplementationPlan

### CrossProjectSequencing
1. **AuditStatuses**
   - Current DB enums (Laravel migrations):
     - `lost_items.status`: `open | matched | closed`.
     - `found_items.status`: `unclaimed | matched | returned`.
     - `claims.status`: `pending | approved | rejected | withdrawn`.
     - `matches.status`: `pending | confirmed | rejected`.
   - Flow-required states (flow.md): `LOST_REPORTED`, `RESOLVED`, `FOUND_UNCLAIMED`, `CLAIM_PENDING`, `CLAIM_APPROVED`, `COLLECTED`, plus claim states `PENDING | APPROVED | REJECTED`.
   - Gaps:
     - Need explicit `RESOLVED` for lost items and `COLLECTED`/`CLAIM_PENDING`/`CLAIM_APPROVED` for found items.
     - Need AI/Flutter enums to mirror backend strings (currently Flutter file placeholder, AI service status filters unknown).
   - Actions:
     - Create new migration to add/modify enums and backfill data (map `open→LOST_REPORTED`, `matched→RESOLVED?` etc).
     - Introduce shared PHP enum (e.g., `App\Enums\LostItemStatus`) and Dart enum definitions generated from single source (maybe JSON schema). Until codegen exists, maintain a YAML/JSON at `shared/statuses.json` consumed by build scripts.
     - Update Laravel models/resources to use constants, update API transformers, seeders, and tests.
     - Coordinate Flutter `lib/features/lost_found/item/domain/enums/item_status.dart` to add same values; expose conversion helpers for UI filters.
     - Update AI FastAPI config to ignore `RESOLVED`/`COLLECTED` in candidate sets and to surface statuses in response payloads for debugging.
2. **TransitionMatrix**
   - Document target transitions in `docs/reminder_workflow.md` + new diagram in `docs/diagrams/usecase.puml`:
     - Lost: `LOST_REPORTED → (match) → RESOLVED`.
     - Found: `FOUND_UNCLAIMED → CLAIM_PENDING → {CLAIM_APPROVED → COLLECTED | CLAIM_PENDING → FOUND_UNCLAIMED (if rejection/cancel)}`.
     - Claims: `PENDING → APPROVED/REJECTED`, with `APPROVED → CANCELLED` edge requiring found item rollback.
   - Implementation steps:
     - Create Laravel domain service (e.g., `FoundItemStatusService`) encapsulating transition guards, using DB row locks (`->lockForUpdate()`) during approve/collect actions.
     - Add transition tests (unit + feature) verifying invalid jumps throw domain exceptions.
     - Update Flutter state machine docs so UI routes align with the same graph (badge colors, CTA availability).
     - AI service should treat status transitions as triggers for recompute jobs; add webhook/queue consumer spec.
3. **EventContracts**
   - Define canonical event payload spec in `docs/events.md` (new file) plus JSON schema for:
     - `claim.submitted`, `claim.approved`, `claim.rejected`, `found.collectionReminder`, `found.collected`.
   - Each payload should include: entity ids, status, timestamps, involved user ids, item summary, optional instructions, and idempotency key.
   - Update Laravel jobs (`SendNotificationJob`, `AIService`, reminder jobs) to publish/consume these events via queue or websockets; ensure the web dashboard and Flutter app listen to the right channels (Pusher/Firebase topics).
   - Document how AI FastAPI subscribes (HTTP webhook from Laravel or polling) and what it responds with.
   - Add versioning + backward compatibility plan so mobile/web deployments can roll independently.
   - **Reference**: see `docs/events.md` for the full envelope + payload schemas.

### LaravelCampusNav
1. **ModelEnumsAndScopes**
   - Add PHP backed enums (e.g., `app/Enums/LostItemStatus.php`, `FoundItemStatus.php`, `ClaimStatus.php`). Reference them inside `app/Models/FoundItem.php`, `LostItem.php`, `ClaimedItem.php`, and any resource transformers or policies.
   - Replace raw strings with enum casts (`protected $casts = ['status' => FoundItemStatus::class];`) and add helpers: `isClaimPending()`, `markClaimApproved(User $admin)`, `markCollected(User $staff, Carbon $ts)`, etc.
   - Ensure relationships eager-load where needed (claims + claimant + approvedBy) to support dashboard KPIs without N+1 queries (`withCount(['pendingClaims','claims as approved_claims_count' => fn...])`).
   - Update factories/seeders/tests to use enums, and add validation rules referencing the enum values (e.g., `Rule::enum(FoundItemStatus::class)`).
   - **Flow Mapping**: The `.cursor/flow.md` steps (`LOST_REPORTED → RESOLVED`, `FOUND_UNCLAIMED → … → COLLECTED`, claim notification timeline) should be reflected in controllers/jobs. Keep this section validated whenever flow.md changes.
2. **StatusTransitions**
   - Introduce a domain service (`app/Services/LostFound/FoundItemFlowService.php`) coordinating claim create/approve/collect flows using DB transactions + `->lockForUpdate()` on `FoundItem` and related `ClaimedItem` rows.
   - Claim create handler (controller or job):
     - Assert no existing pending claim from same user for same found item (DB unique index + application check).
     - Create `ClaimedItem`, set `found_items.status = CLAIM_PENDING`, enqueue `SendNotificationJob` (admin + claimant) and recompute AI matches (optional).
   - Claim approve handler:
     - Load found item + pending claims with lock.
     - Approve chosen claim, mark others rejected, store audit log rows, dispatch `ClaimApproved` notification, schedule `SendCollectionReminderJob` chain via `collection_deadline` config.
   - Claim reject/cancel handler:
     - If no other pending claims remain, revert found item to `FOUND_UNCLAIMED`.
   - Collection handler:
     - Mark found item `COLLECTED`, set `collected_at/by`, link to approved claim, resolve linked lost item (`status = RESOLVED`), trigger analytics counters and `found.collected` event.
   - All handlers should accept an idempotency key (header/body) to avoid double-processing and log to `activity_logs`.
3. **NotificationsAndReminders**
   - ✅ Update `app/Jobs/SendNotificationJob.php`, `SendCollectionReminderJob.php`, `MonitorPendingClaimsSlaJob.php`, `ProcessOverdueCollectionsJob.php` to dispatch the new event payloads defined earlier; ensure they respect the new statuses when querying.
   - ✅ Enhance `app/Services/FcmService.php` to publish to claimant/admin topics with structured data (status, item summary, CTA deep links).
   - ✅ Add scheduler entries (`app/Console/Kernel.php`) for SLA monitors (e.g., check pending claims every 10 min, overdue collections hourly).
4. **APIResponses**
   - ✅ Update routes/controllers under `routes/api.php` (claims, found items) to return the post-action entity snapshot (`FoundItemResource` with nested claims + status).
   - ✅ Add request validation rules to block duplicates and enforce status-based permissions (only admins can approve, only claimants can cancel).
   - ✅ Extend `app/Http/Resources` to include new fields: `collection_deadline`, `claim_status_summary`, `transition_history` (optional) so Flutter has all data needed.
5. **AuditingAndAnalytics**
   - ✅ Use/extend `app/Models/ActivityLog.php` to record every transition with actor, previous state, new state, metadata (claim id, reason).
   - ✅ Update analytics counters (DB table or cached metrics) when `found_items` transition to `CLAIM_PENDING`, `CLAIM_APPROVED`, `COLLECTED`, and when lost items resolve; expose these in dashboard widgets (`resources/views/dashboard.blade.php`).
   - ✅ Ensure audit + analytics data is included in exports/reports if applicable (maybe `docs/modification_plan.md` references).
6. **AdminUiParity** ✅
   - Blade views (`item.blade.php`, `edit-item.blade.php`, `components/item-table.blade.php`, `admin/claims/index.blade.php`) now use the enum values/labels everywhere (filters, badges, exports, modals), preventing legacy strings from leaking through UI.
7. **GuardRailsAndTests** ✅
   - Controllers/services (`Admin/ClaimsController`, `Api/ItemController`, `Jobs/ProcessOverdueCollectionsJob`, `SyncClaimedItemsJob`) refactored to funnel every mutation through `FoundItemFlowService` helpers, with DB transactions plus row locks on `FoundItem`/`ClaimedItem`.
   - PHPUnit coverage in place:
     - **Unit**: `tests/Unit/FoundItemFlowServiceTest.php` exercises all legal transitions + rejection of invalid approvals.
     - **Feature**: `tests/Feature/ClaimTransitionTest.php` covers claim creation → pending, admin approve → CLAIM_APPROVED (and competitor rejection), cancel approval → FOUND_UNCLAIMED, mark collected → COLLECTED + linked lost item resolution.
     - **Concurrency**: still tbd (needs follow-up harness with `Bus::fake()` and `lockForUpdate` assertion hooks).
   - Shared helpers (`DomainEventService`, `AnalyticsCounter`) wired so CI/env parity sticks once concurrency suite lands.
8. **BackendWorkPlan**
   - **Short-term (DONE)**: Align controllers/jobs + add the guard-rail PHPUnit suite noted above (pending the explicit concurrency harness).
   - **Mid-term (IN PROGRESS)**: API resources expose transition history, notification payloads use `docs/events.md`, reminder/overdue jobs emit the canonical events, and analytics counters stay in sync via observers.
   - **Coordination (OPEN)**: Once Flutter/AI parity work kicks off, open tickets referencing the updated API payloads/tests plus QA checklist from `TestingAndVerification`.

### FlutterNavistfind
1. **StatusModelParity**: Fill `lib/features/lost_found/item/domain/enums/item_status.dart` using same constants; propagate to UI view models and GraphQL/REST DTOs.
2. **LostListScreen**: Filter cards to show `LOST_REPORTED` and `RESOLVED` buckets, add state badges, and handle updated API payload shape.
3. **FoundItemListing**: Display only `FOUND_UNCLAIMED` + `CLAIM_PENDING`, showing competition indicator; hide `CLAIM_APPROVED`/`COLLECTED`.
4. **ClaimSubmissionFlow**: Prevent duplicate claims (disable button when claim exists), show confirmation banner (“Claim submitted — waiting for admin”), and subscribe to push notifications for status updates.
5. **ClaimStatusScreens**: Build dedicated UI per status (Pending editable evidence/cancel, Approved with pickup instructions, Rejected with guidance). Integrate push notification handler routing to these views.
6. **TransitionHandling & Push Hooks**: Update notification listener to parse the enum strings from FCM payloads, drive navigation (e.g., `CLAIM_APPROVED` → pickup screen, `CLAIM_PENDING` → claim detail). Ensure offline cache mirrors backend transitions so UI never shows impossible actions (e.g., disable claim button locally when status=CLAIM_PENDING + user already claimant).
7. **AI Suggestions**: After lost item submission, call the AI recommendation endpoint and filter out matches where found status ≠ `FOUND_UNCLAIMED`. When user claims from suggestions, guard against stale statuses by re-fetching item detail and showing a “claim already pending” message if backend returns non-`FOUND_UNCLAIMED`.

### AIServiceNavistfind
1. **SimilarityPipeline**: Ensure FastAPI endpoint ingests both found and lost item metadata + statuses, ignoring resolved/collected pairs.
2. **ClaimTriggeredRecompute**: When a new claim or status change occurs, enqueue AIService recomputation via Laravel `AIService.php`; include retries and idempotency keys.
3. **RecommendationAPI**: Expose endpoint returning ranked matches for a lost report; Flutter should call it post submission and optionally show suggestions.
4. **Monitoring**: Log match quality metrics and surface errors to Laravel for alerting; add health endpoint consumed by Ops.

### TestingAndVerification
1. **UnitTests**: Cover claim creation transition, approval (single WIN), collection auto-resolution, notification dispatch.
2. **IntegrationTests**: Simulate concurrent approvals (assert locking), and approve → collect pipeline verifying analytics + notifications.
3. **E2ETests**: Script full student + admin + AI happy path plus SLA reminders and cancellation edge cases.

### DeliveryChecklist
1. Update architecture diagrams in `docs/diagrams` with new statuses and event flow.
2. Provide migration + seeder updates for status enums and demo data.
3. Document API contract changes in `README.md` or `/docs`.
4. Coordinate deployment order: DB migrations → Laravel backend → AI service → Flutter release; ensure feature flags/maintenance windows if required.

