# NavistFind Status System Guide

 _Last updated: 2025-11-13_

This document describes the finalized status model that keeps the NavistFind ecosystem (mobile app, admin dashboard, and AI services) in sync. It is the canonical reference for engineers working on the Flutter client, Laravel dashboard, or the SBERT-powered FastAPI matcher.

---

## 1. Lost Item Statuses (Mobile Users)

Lost items only require two states, which keeps the mobile UX intuitive and eliminates “half-finished” states in the student-facing timeline.

| Status | Description | Set By |
| --- | --- | --- |
| `LOST_REPORTED` | A student submitted a lost-item report. It is still awaiting a successful hand-off. | Mobile user |
| `RESOLVED` | The matched found item was physically collected. | Admin / automation |

**Why only two statuses?**

- Mobile users don’t interact with staff workflows (claim triage, verification, reminders).
- Students should only see whether their case is still open or fully resolved.
- Fine-grained tracking happens on the Found Item and Claim Request layers.

**Auto-resolution rule**

```php
if ($foundItem->status === 'COLLECTED') {
    $lostItem->status = 'RESOLVED';
    $lostItem->save();
}
```

### Lost Item Transition Flow

```
LOST_REPORTED  ──(Found item marked COLLECTED)──▶  RESOLVED
```

---

## 2. Found Item Statuses (Admin Dashboard)

Found items carry the operational workflow. Every status change is driven either by admin action or automation (reminders, SLA monitors, AI matches).

| Status | Description | Transition Trigger |
| --- | --- | --- |
| `FOUND_UNCLAIMED` | Item was posted by staff; no active claims. Visible to all students. | Initial state or after cancellation/reopen |
| `CLAIM_PENDING` | One or more claim requests exist. Admin must review evidence. Item still visible so other students may claim. | Student submits claim |
| `CLAIM_APPROVED` | Admin approved a claim. Item is hidden from the recommendation feed and collection reminders/SLA timers start here. | Admin approval |
| `CLAIM_REJECTED` | Admin rejected the active claim. Either another claim is promoted, or the item is reopened to `FOUND_UNCLAIMED`. | Admin rejection |
| `COLLECTED` | Staff verified hand-off. Triggers Lost Item auto-resolution and archival. | Admin marks collected |

### Found Item Transition Rules

```
FOUND_UNCLAIMED
    ├─(Student claim submitted)────────────▶ CLAIM_PENDING
    ├─(Admin reopens after cancellation)───▶ FOUND_UNCLAIMED (loop)
    ▼
CLAIM_PENDING
    ├─(Admin approves)─────────────────────▶ CLAIM_APPROVED
    ├─(Admin rejects)──────────────────────▶ CLAIM_REJECTED
    └─(All claims rejected)────────────────▶ FOUND_UNCLAIMED
CLAIM_APPROVED
    ├─(Admin sends reminders / SLA monitor)│
    ├─(Admin cancel approval)──────────────▶ FOUND_UNCLAIMED
    └─(Admin marks collected)──────────────▶ COLLECTED
CLAIM_REJECTED
    ├─(Next pending claim exists)──────────▶ CLAIM_PENDING
    └─(No more claims)─────────────────────▶ FOUND_UNCLAIMED
COLLECTED
    └─(Auto rule)──────────────────────────▶ Lost item → RESOLVED
```

Notes:
- Only `CLAIM_APPROVED` items are hidden from the student feed.
- SLA alerts and collection reminders operate exclusively when a found item sits in `CLAIM_APPROVED`.
- Reopen/cancel flows always reset the status to `FOUND_UNCLAIMED`, removing reminder timers and re-enabling AI matches.

---

## 3. Claim Request Statuses

Claim requests encapsulate each student’s assertion of ownership. They have their own lifecycle, independent of the host found item.

| Status | Description | Why separate? |
| --- | --- | --- |
| `PENDING` | Student submitted evidence; waiting for admin review. | Multiple students can compete for the same found item. |
| `APPROVED` | Admin selected this claim as the winner. | Needed for audit history even after item is collected. |
| `REJECTED` | Claim was denied (insufficient proof, conflicting info, etc.). | Enables rejection messaging, analytics, and re-claim attempts. |

**Separation rationale**

- A found item may have many claims; each claim must track its own evaluation outcome.
- Claim history powers analytics (fraud detection, guidance quality) without polluting found item states.
- Messaging (push/email) uses the claim status to craft precise feedback.

### Claim Request Transition Flow

```
PENDING
    ├─(Admin approves)──▶ APPROVED
    └─(Admin rejects)───▶ REJECTED
```

Approved claim requests remain “APPROVED” even after the item is marked `COLLECTED`, providing an immutable audit trail.

---

## 4. Status Transition Summary

| Entity | Allowed States | Entry Point | Exit Point |
| --- | --- | --- | --- |
| Lost Item | `LOST_REPORTED`, `RESOLVED` | Mobile submission | Auto-resolve when matched found item is `COLLECTED` |
| Found Item | `FOUND_UNCLAIMED`, `CLAIM_PENDING`, `CLAIM_APPROVED`, `CLAIM_REJECTED`, `COLLECTED` | Staff posting | Archive after `COLLECTED` |
| Claim Request | `PENDING`, `APPROVED`, `REJECTED` | Student claim | Historical record (no delete) |

---

## 5. Relationship: Lost → Found → Claim

| Link | Description |
| --- | --- |
| Lost ↔ Found | AI service (SBERT + FastAPI) generates similarity matches. When admins approve a claim and eventually collect the item, the linked lost entry is marked `RESOLVED`. |
| Found ↔ Claim Requests | Each found item can have multiple claim rows. Admin review operates at the claim level, and status promotion/demotion drives the found item state machine. |
| Lost Items vs Claim Workflow | Mobile lost reports do **not** pass through claim approval. Students simply wait for staff to confirm that a found item was collected. This keeps the student UX decoupled from staff operations. |

**Diagram**

```
Lost Item (LOST_REPORTED)
    │
    │  AI similarity (SBERT)
    ▼
Found Item (FOUND_UNCLAIMED)
    │
    ├─ Student claim(s) ─▶ Claim Request (PENDING → APPROVED/REJECTED)
    │
    └─ When claim APPROVED + collected ─▶ Found Item (COLLECTED)
                                            │
                                            └─ Auto: Lost Item (RESOLVED)
```

---

## 6. Auto-Resolution Logic

- When a staff member marks a found item as `COLLECTED`, the system immediately:
  1. Triggers the auto-resolution rule for the linked lost item.
  2. Updates analytics (dashboard “Collected” card, reminder conversions).
  3. Archives the found entry from active queues.
- If the collection fails or is canceled:
  - The found item returns to `FOUND_UNCLAIMED`.
  - Claim reminders/SLA timers stop.
  - The lost item remains `LOST_REPORTED`.

---

## 7. Summary Tables

### Lost Items (Mobile)

| Status | Visible to Student? | Description |
| --- | --- | --- |
| `LOST_REPORTED` | Yes | Active case; waiting for staff confirmation. |
| `RESOLVED` | Yes | Matching found item has been collected. |

### Found Items (Admin)

| Status | Visible to Students? | Key Automation |
| --- | --- | --- |
| `FOUND_UNCLAIMED` | Yes | AI matching candidates |
| `CLAIM_PENDING` | Yes | SLA countdown starts (24h review) |
| `CLAIM_APPROVED` | No | Reminder scheduler, overdue monitor |
| `CLAIM_REJECTED` | Maybe (if other claims exist) | Next claim promotion |
| `COLLECTED` | No | Lost item resolution, archival |

### Claim Requests

| Status | Notification Channel | Notes |
| --- | --- | --- |
| `PENDING` | Push + email (“Claim received”) | Student can edit or add more info. |
| `APPROVED` | Push + email (“Pick up instructions”) | Drives found item → `CLAIM_APPROVED`. |
| `REJECTED` | Push + email (“Provide more proof”) | Optionally links to guidance docs. |

---

## 8. Developer Checklist

1. **Mobile App**
   - Display only `LOST_REPORTED` / `RESOLVED`.
   - Consume “claim approved” emails/push as informational notifications.
2. **Web Dashboard**
   - Use the table tabs: Pending = `CLAIM_PENDING`, Approved = `CLAIM_APPROVED`, Collected = `COLLECTED`.
   - Action buttons (approve / reject / send reminder / cancel / mark collected) must update statuses per this document.
3. **AI Service**
   - Feed only `FOUND_UNCLAIMED` or `CLAIM_PENDING` items into the matcher.
   - Remove `CLAIM_APPROVED` and higher states from the candidate set.
4. **Automation**
   - Reminder jobs work only on `CLAIM_APPROVED`.
   - SLA alerts fire when `CLAIM_PENDING` exceeds 24 hours.
   - Auto-resolution keeps lost items in sync with the collection pipeline.

---

By adhering to the status definitions and transitions above, all components (Flutter app, Laravel admin, FastAPI matcher) will share a consistent mental model and data contract. Reach out to the platform team before introducing new statuses or altering transitions.
 