1) High-level runtime sequence (quick view)

Student posts lost item → lost_items.status = 'LOST_REPORTED'.

Admin posts found item → found_items.status = 'FOUND_UNCLAIMED'. AI matcher computes similarity and notifies the student.

Student files a claim for a found item → a claim_requests row is created with status = 'PENDING' and found_items.status → CLAIM_PENDING (if not already).

Admin reviews claim(s) → approves one (APPROVED) or rejects (REJECTED). Approving a claim sets found_items.status = 'CLAIM_APPROVED'.

Admin marks item collected after physical hand-off → found_items.status = 'COLLECTED'. System runs auto-resolution: linked lost_items.status = 'RESOLVED' and archive/analytics steps run.

2.) Flutter client behaviors & UI flows

Lost list screen: show LOST_REPORTED cards and RESOLVED cards under lost and found page.

Notifications: when user receives claim_status notification:

PENDING: show claim details screen with ability to edit evidence cancel request.

APPROVED: show pick-up instructions + highlight that admin marked a claim approved (but still must go to admin to collect).

REJECTED: show editable message to improve claim evidence and cancel request .

Found item listing: students see only FOUND_UNCLAIMED and CLAIM_PENDING (i want to show competition); hide CLAIM_APPROVED and COLLECTED.

Claim submission flow:


After success: show "Claim submitted — waiting for admin".

Back-end returns claim id; App subscribes to push notifications.

Edge UI: prevent duplicate claims by the same user for same found item (UI-level disable + backend validation).

3) Notification & automation timeline

When claim created → send push claim_received to admin and claim_submitted to claimant.

When claim approved → send push claim_approved with pickup instructions to claimant; send claim_rejection to others.

When claim rejected → send push claim_rejected to claimant.

When found item approved → schedule reminders (e.g., check after 24h) and start SLA monitoring.

When collected → send collected_confirmation to claimant and archival notice to admin staff.

4) Concurrency, locking, and race conditions

Locking: use DB row-level locks (e.g. SELECT ... FOR UPDATE / lockForUpdate() in Laravel) when mutating found_items or approving claims to avoid race where two admins approve different claims simultaneously.

Idempotency: mark admin actions with audit idempotency keys (e.g., action_request_id) so repeated requests don’t duplicate transitions.

Optimistic checks: when approving, check found_item.status is not already CLAIM_APPROVED or COLLECTED.

Retries: all notification jobs and external AI calls should be queued with retry policies.

5) Audit logs & analytics

Always persist an audit row on major transitions:

Keep claim_requests immutable after resolution (don’t delete; only mark archived). This helps with fraud analysis.

Update analytics counters on COLLECTED and RESOLVED.

6) Edge cases & rules (explicit)

Multiple claims approved: NEVER allow more than one APPROVED claim for the same found item. Approve action should reject other PENDING claims automatically.

Approve then cancel: If admin cancels approval before collection, revert found_items.status → FOUND_UNCLAIMED and set claim to REJECTED (or CANCELLED if you want). Notify user.

Collection fail: If claimant fails to collect in defined SLA, admin can cancel approval → found item returns to FOUND_UNCLAIMED.

Mismatched link: If no lost_item_id linked but a user claims, you might keep claim without linking to lost items (useful when claimant did not post a lost report). Only set lost_item.status = RESOLVED when a clear link exists.

Anonymous found post: Still allow claims; claim can be standalone without a lost_item and becomes APPROVED → COLLECTED without auto-resolving any lost record.

7) Tests & verification checklist

Unit tests:

Claim creation toggles found_items.status from FOUND_UNCLAIMED → CLAIM_PENDING.

Approving a claim sets found item → CLAIM_APPROVED and other pending claims → REJECTED.

Marking COLLECTED sets linked lost_items.status → RESOLVED.

Integration tests:

Simulate two concurrent claim approvals — assert only one accepted.

Simulate approve → collect → ensure analytics increment and notifications sent.

E2E:

Full happy-path: Student lost → AI match → Claim → Admin approve → Admin mark collected → Student receives RESOLVED.

8) Helpful implementation tips

Enums: store status as enums in DB and keep matching constants in Laravel Model::STATUS_* and Flutter models for strong typing.

Event-driven: emit domain events (ClaimApproved, FoundCollected) and subscribe handlers for notifications, analytics updates, and AI-index changes.

SLA timers: use scheduled jobs to check CLAIM_PENDING older than 24h → send slack/email to admin or escalate.

API design: always return the full current state for the entity after actions (avoid clients needing extra fetches).

Soft deletes: prefer archived boolean for found items so historical audit data remains accessible.