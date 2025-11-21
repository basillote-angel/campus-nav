# Mobile App (Flutter) Notification Contract

This document outlines all push notification types sent by the Laravel backend and how the Flutter mobile app should handle them according to the claim flow defined in `flow.md`.

## Notification Payload Structure

All notifications sent via `SendNotificationJob` follow this FCM payload structure:

```json
{
  "notification": {
    "title": "Human-readable title",
    "body": "Human-readable message body"
  },
  "data": {
    "type": "notificationType",
    "related_id": "123",  // Claim ID, Found Item ID, or Lost Item ID depending on type
    "score": null,  // Optional: similarity score for AI matches
    "notification_id": "456"  // AppNotification database ID for marking as read
  }
}
```

## Notification Types by User Role

### Student/User Notifications

#### `claimSubmitted`
**When**: Immediately after a student submits a claim for a found item.

**Payload**:
- `related_id`: Claim ID (`ClaimedItem.id`)
- `notification_id`: AppNotification ID

**Expected Behavior**:
- Navigate to claim details screen showing status = `PENDING`
- Show confirmation message: "Claim submitted — waiting for admin"
- Display editable claim message/evidence (allow cancel request if needed)
- Subscribe to further notifications for this claim ID

**Screen**: `ClaimDetailsScreen` (status: PENDING)

---

#### `claimApproved`
**When**: Admin approves the student's claim.

**Payload**:
- `related_id`: **Found Item ID** (e.g., `32`)
- `notification_id`: AppNotification ID
- **Note**: Body contains detailed pickup instructions with office location, hours, deadline, ID requirements

**Expected Behavior**:
- **API Call**: `GET /api/items/{related_id}` (e.g., `GET /api/items/32`)
  - ✅ **Correct**: `GET /api/items/32`
  - ❌ **Wrong**: `GET /api/items/32/claim` (that endpoint only supports POST)
- Navigate to approved claim details screen
- Highlight pickup instructions prominently
- Display collection deadline prominently
- Show office location, hours, and contact info
- Allow student to view/copy pickup instructions
- **Important**: Item is NOT yet collected; student must physically go to admin office

**Screen**: `ClaimDetailsScreen` (status: APPROVED) or `PickupInstructionsScreen`

**Related Status**: Found item status = `CLAIM_APPROVED`

---

#### `claimRejected`
**When**: Admin explicitly rejects the student's claim OR another claimant was approved (losing claim).

**Payload**:
- `related_id`: **Found Item ID** (e.g., `32`)
- `notification_id`: AppNotification ID
- **Note**: Body contains rejection reason and guidance

**Expected Behavior**:
- **API Call**: `GET /api/items/{related_id}` (e.g., `GET /api/items/32`)
  - ✅ **Correct**: `GET /api/items/32`
  - ❌ **Wrong**: `GET /api/items/32/claim` (that endpoint only supports POST)
- Navigate to rejection details screen
- Display admin's rejection reason clearly
- Show guidance on how to improve claim evidence
- Allow student to:
  - Submit a new claim with better evidence
  - Cancel the request entirely
- If rejection reason mentions "Another claimant was approved", explain the competitive claim scenario

**Screen**: `ClaimRejectedScreen` or `ClaimDetailsScreen` (status: REJECTED)

**Related Status**: Claim status = `REJECTED`

---

#### `collectionReminder`
**When**: Automated reminder sent 3 days before collection deadline OR 1 day before deadline.

**Payload**:
- `related_id`: **Found Item ID** (e.g., `32`)
- `notification_id`: AppNotification ID
- **Note**: Body contains deadline, office location, office hours, contact info

**Expected Behavior**:
- **API Call**: `GET /api/items/{related_id}` (e.g., `GET /api/items/32`)
  - ✅ **Correct**: `GET /api/items/32`
  - ❌ **Wrong**: `GET /api/items/32/claim` (that endpoint only supports POST)
- Navigate to approved claim/pickup instructions screen
- Highlight that deadline is approaching
- Show countdown to deadline (if within 24h, make it urgent)
- Display reminder with office details again

**Screen**: `ClaimDetailsScreen` (status: APPROVED) or `PickupInstructionsScreen`

**Reminder Stages**:
- `three_day`: 3 days before deadline
- `one_day`: 1 day before deadline
- `manual`: Admin manually triggered reminder

---

#### `collectionOverdue`
**When**: Collection deadline has passed (grace period still active).

**Payload**:
- `related_id`: Found Item ID
- `notification_id`: AppNotification ID

**Expected Behavior**:
- Navigate to approved claim screen with urgent warning
- Show that deadline has passed but grace period is still active
- Encourage immediate contact with admin office
- Display contact email/phone prominently
- Warn that further delay may result in claim cancellation

**Screen**: `ClaimDetailsScreen` (status: APPROVED) with overdue warning

---

#### `collectionExpired`
**When**: Claim was approved but item not collected within extended grace period (typically 14+ days past deadline). Item is now reopened.

**Payload**:
- `related_id`: Found Item ID
- `notification_id`: AppNotification ID

**Expected Behavior**:
- Navigate to item details screen (not claim screen)
- Show that the claim has expired and item is available again
- Allow student to submit a new claim if they still need the item
- Explain why the claim expired (deadline missed)

**Screen**: `ItemDetailsScreen` (item status = `FOUND_UNCLAIMED`) with expired claim notice

**Related Status**: Found item status = `FOUND_UNCLAIMED` (reopened)

---

#### `collectionConfirmed`
**When**: Admin marks the item as collected after physical hand-off.

**Payload**:
- `related_id`: Found Item ID
- `notification_id`: AppNotification ID

**Expected Behavior**:
- Navigate to success/completion screen
- Show confirmation that item was successfully collected
- Update UI to mark item/claim as `COLLECTED` / `RESOLVED`
- If linked to a lost item, mark that lost item as `RESOLVED`
- Thank the user for using NavistFind

**Screen**: `ClaimSuccessScreen` or `ItemDetailsScreen` (status: COLLECTED)

**Related Status**: 
- Found item status = `COLLECTED`
- Linked lost item (if any) status = `RESOLVED`

---

### Admin/Staff Notifications

#### `newClaim`
**When**: New claim submitted by a student (first claim on an item).

**Payload**:
- `related_id`: Found Item ID
- `notification_id`: AppNotification ID
- **Note**: Body contains claimant name, email, item title, category, location, claim message preview

**Expected Behavior** (if admin mobile app exists):
- Navigate to claims review screen
- Filter/show pending claims
- Highlight the new claim with claimant details

**Screen**: `AdminClaimsScreen` (filter: PENDING)

---

#### `multipleClaims`
**When**: Item already has a pending claim and another student submits a competing claim.

**Payload**:
- `related_id`: Found Item ID
- `notification_id`: AppNotification ID

**Expected Behavior** (if admin mobile app exists):
- Navigate to claims review screen
- Filter to show items with multiple claims
- Highlight that this item has competing claims (admin needs to choose winner)

**Screen**: `AdminClaimsScreen` (filter: PENDING, highlight: multiple claims)

---

#### `pendingClaimSla`
**When**: A claim has been pending for longer than the SLA threshold (default: 24 hours).

**Payload**:
- `related_id`: Found Item ID
- `notification_id`: AppNotification ID
- **Note**: Body mentions how long the claim has been waiting

**Expected Behavior** (if admin mobile app exists):
- Navigate to claims review screen
- Filter/sort to show oldest pending claims first
- Alert admin to review and take action

**Screen**: `AdminClaimsScreen` (filter: PENDING, sort: oldest first)

---

#### `collectionOverdueAdmin`
**When**: Collection deadline has passed but item is not yet collected.

**Payload**:
- `related_id`: Found Item ID
- `notification_id`: AppNotification ID

**Expected Behavior** (if admin mobile app exists):
- Navigate to approved claims screen
- Filter to show overdue items
- Alert admin to follow up with claimant

**Screen**: `AdminClaimsScreen` (filter: APPROVED, highlight: overdue)

---

#### `collectionReopened`
**When**: Item was reopened after claimant missed collection deadline (grace period expired).

**Payload**:
- `related_id`: Found Item ID
- `notification_id`: AppNotification ID

**Expected Behavior** (if admin mobile app exists):
- Navigate to items list
- Show that item is back to `FOUND_UNCLAIMED`
- Allow admin to view audit history

**Screen**: `AdminItemsScreen` (item status: FOUND_UNCLAIMED)

---

#### `collectionArchived`
**When**: Item was marked as collected by an admin (archival notice to all admins/staff).

**Payload**:
- `related_id`: Found Item ID
- `notification_id`: AppNotification ID

**Expected Behavior** (if admin mobile app exists):
- Navigate to collected items screen
- Show completion summary
- Update analytics dashboard if applicable

**Screen**: `AdminClaimsScreen` (filter: COLLECTED)

---

## Status Transition Reference

### Found Item Statuses
- `FOUND_UNCLAIMED` → Item is available for claims
- `CLAIM_PENDING` → One or more claims are pending admin review
- `CLAIM_APPROVED` → A claim was approved; awaiting physical collection
- `COLLECTED` → Item was collected by claimant at admin office

### Claim (ClaimedItem) Statuses
- `PENDING` → Waiting for admin review
- `APPROVED` → Admin approved this claim
- `REJECTED` → Admin rejected this claim or another claimant won

### Lost Item Statuses
- `LOST_REPORTED` → Student reported item as lost
- `RESOLVED` → Found item was collected and linked lost item is resolved

---

## Navigation Flow Examples

### Happy Path: Claim Submission → Approval → Collection

1. Student submits claim → receives `claimSubmitted` → navigate to `ClaimDetailsScreen` (PENDING)
2. Admin approves → student receives `claimApproved` → navigate to `PickupInstructionsScreen`
3. Reminder sent → student receives `collectionReminder` → navigate to `PickupInstructionsScreen` (highlight deadline)
4. Admin marks collected → student receives `collectionConfirmed` → navigate to `ClaimSuccessScreen` (RESOLVED)

### Rejection Path

1. Student submits claim → receives `claimSubmitted` → navigate to `ClaimDetailsScreen` (PENDING)
2. Admin rejects → student receives `claimRejected` → navigate to `ClaimRejectedScreen`
3. Student can submit new claim with better evidence or cancel

### Competitive Claim Path

1. Student A submits claim → receives `claimSubmitted`
2. Student B submits competing claim → Student A receives `claimSubmitted` (their claim still pending)
3. Admin approves Student B → Student A receives `claimRejected` (explains another claimant won)
4. Student B receives `claimApproved` → follows happy path

---

## API Endpoints for Mobile App

### Submit Claim
```
POST /api/items/{id}/claim
Body: {
  "message": "Claim description/evidence",
  "contactName": "Optional contact name",
  "contactInfo": "Optional contact details",
  "matchedLostItemId": null  // Optional: link to lost item report
}
Response: {
  "claimId": 123,
  "message": "Claim submitted. Admin will review shortly.",
  ...
}
```

### Get Notifications
```
GET /api/notifications
Response: Array of AppNotification objects

GET /api/notifications/updates
Response: Array of unread notifications
```

### Mark Notification as Read
```
POST /api/notifications/{id}/read
POST /api/notifications/mark-all-read
```

### Get Claim/Item Details

**When clicking a notification with `related_id`:**
```
GET /api/items/{related_id}
```

**Response**: FoundItem resource with nested claims, status, collection deadline, etc.

**Example for `claimApproved` notification:**
- Notification has: `related_id: 32` (Found Item ID)
- Navigate to: `GET /api/items/32`
- **NOT** `GET /api/items/32/claim` (that endpoint is POST only for submitting claims)

**Response includes:**
- Item details (title, description, status, etc.)
- Claims array (includes user's claim with status, approval details)
- Collection deadline and instructions
- Transition history

---

## Implementation Checklist for Flutter

- [ ] Handle all student notification types (`claimSubmitted`, `claimApproved`, `claimRejected`, `collectionReminder`, `collectionOverdue`, `collectionExpired`, `collectionConfirmed`)
- [ ] Implement deep linking from notifications to appropriate screens
- [ ] Display pickup instructions prominently when claim is approved
- [ ] Show deadline countdown/urgency for reminders and overdue alerts
- [ ] Allow claim editing/cancellation for PENDING claims
- [ ] Handle competitive claim scenarios (multiple pending → one approved)
- [ ] Update UI state when `collectionConfirmed` is received (mark as RESOLVED)
- [ ] Subscribe to push notifications on app startup if user is authenticated
- [ ] Store device token via `POST /api/device-tokens` on app install/login
- [ ] Poll `GET /api/notifications/updates` periodically or use WebSocket if implemented
- [ ] Mark notifications as read when user views them

---

## Notes

1. **Automatic Reminders**: The backend automatically sends `collectionReminder` at 3 days and 1 day before deadline. No action needed from student.

2. **Grace Period**: After deadline passes, there's a grace period (configurable, default ~72 hours) before the claim expires. During this time, `collectionOverdue` is sent. After grace period, `collectionExpired` is sent and item is reopened.

3. **Status Consistency**: Always sync item/claim status after receiving a notification. The `related_id` points to the entity that changed.

4. **Multiple Notifications**: A student may receive multiple notifications for the same claim/item. Handle gracefully (e.g., don't show duplicate screens if already on the relevant screen).

5. **Offline Handling**: Queue notification handlers if app is offline. Process when connection is restored.

---

**Last Updated**: Based on Laravel backend implementation as of latest notification wiring.

