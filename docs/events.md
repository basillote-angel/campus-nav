## Lost & Found Domain Event Contracts

All backend services publish structured JSON events whenever major state changes occur. Each event shares the same envelope plus a domain-specific payload.

### Event Envelope

```json
{
  "eventId": "uuid-v4",
  "eventType": "claim.approved",
  "occurredAt": "2025-11-15T08:42:13.000Z",
  "version": "1.0",
  "source": "campus-nav.api",
  "actor": {
    "id": 123,
    "role": "admin"
  },
  "payload": { }
}
```

- `eventId`: unique identifier per dispatch (used for idempotency).
- `eventType`: string from the table below.
- `occurredAt`: ISO 8601 timestamp.
- `version`: semantic version for backward compatibility (`1.x` reserved for current schema).
- `source`: logical emitter (api, job, admin-dashboard).
- `actor`: optional user performing the action.
- `payload`: event-specific data (see sections below).

### Event Type Matrix

| Event Type               | Trigger                               | Consumers                 |
|--------------------------|----------------------------------------|---------------------------|
| `claim.submitted`        | Student submits claim                  | Admin dashboard, AI svc   |
| `claim.approved`         | Admin approves claim                   | Claimant app, analytics   |
| `claim.rejected`         | Admin rejects/cancels claim            | Claimant app              |
| `found.collectionReminder` | Reminder job notifies claimant      | Mobile push, audit log    |
| `found.collected`        | Item marked collected                  | Lost owner, analytics     |

### Payload Schemas

#### `claim.submitted`

```json
{
  "claimId": 456,
  "foundItem": {
    "id": 321,
    "status": "CLAIM_PENDING",
    "title": "Black Backpack"
  },
  "claimant": {
    "id": 789,
    "name": "Jane Doe"
  },
  "message": "Contains laptop + textbooks",
  "submittedAt": "2025-11-15T08:40:00Z"
}
```

#### `claim.approved`

```json
{
  "claimId": 456,
  "foundItem": {
    "id": 321,
    "status": "CLAIM_APPROVED",
    "title": "Black Backpack",
    "collectionDeadline": "2025-11-18T10:00:00Z"
  },
  "claimant": {
    "id": 789,
    "name": "Jane Doe",
    "contactInfo": "0917-123-4567"
  },
  "approvedBy": {
    "id": 12,
    "name": "Admin Smith"
  }
}
```

#### `claim.rejected`

```json
{
  "claimId": 456,
  "foundItem": {
    "id": 321,
    "status": "FOUND_UNCLAIMED"
  },
  "claimant": {
    "id": 789,
    "name": "Jane Doe"
  },
  "reason": "Evidence insufficient",
  "rejectedBy": {
    "id": 12,
    "name": "Admin Smith"
  }
}
```

#### `found.collectionReminder`

```json
{
  "foundItemId": 321,
  "status": "CLAIM_APPROVED",
  "claimantId": 789,
  "collectionDeadline": "2025-11-18T10:00:00Z",
  "reminderStage": "three_day"
}
```

`reminderStage` values: `three_day`, `one_day`, `manual`.

#### `found.collected`

```json
{
  "foundItemId": 321,
  "status": "COLLECTED",
  "claimId": 456,
  "claimantId": 789,
  "collectedAt": "2025-11-15T12:05:00Z",
  "collectedBy": 12,
  "linkedLostItemId": 555,
  "lostItemStatus": "RESOLVED"
}
```

### Versioning & Compatibility
- Increment `version` when payloads change. Minor bump for additive fields (`1.1`), major bump for breaking changes (`2.0`).
- Producers must continue emitting old versions until all consumers upgrade.
- Consumers should ignore unknown fields to remain forward compatible.

### Delivery Channels
- Events are queued via Laravel jobs (`SendNotificationJob`, `AIService`, reminder jobs).
- Internally they can be stored in `activity_logs` for auditing.
- External consumers (Flutter, AI service) subscribe via Firebase, REST webhooks, or polling endpoints; each event includes the full state snapshot to avoid extra fetches.



