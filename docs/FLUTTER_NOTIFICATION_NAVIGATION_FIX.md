# Flutter Notification Navigation Fix

## Issue

When clicking a notification in the Flutter app, it's making an incorrect API call:

**Current (Wrong):**
```
GET /api/items/32/claim
```

**Error:**
```
The GET method is not supported for route api/items/32/claim. Supported methods: POST.
```

## Root Cause

The Flutter app is incorrectly trying to GET the `/api/items/{id}/claim` endpoint, which only supports **POST** (for submitting claims).

## Solution

For notifications with `related_id`, the app should use:

**Correct Endpoint:**
```
GET /api/items/{related_id}
```

## Notification Types and Correct Navigation

### `claimApproved`
- `related_id`: **Found Item ID** (e.g., `32`)
- **Navigate to**: `GET /api/items/32`
- Returns: FoundItem with claims array (includes user's approved claim)

### `claimRejected`
- `related_id`: **Found Item ID** (e.g., `32`)
- **Navigate to**: `GET /api/items/32`
- Returns: FoundItem with claims array (includes user's rejected claim)

### `collectionReminder`
- `related_id`: **Found Item ID** (e.g., `32`)
- **Navigate to**: `GET /api/items/32`
- Returns: FoundItem with collection deadline and instructions

### `collectionOverdue`
- `related_id`: **Found Item ID** (e.g., `32`)
- **Navigate to**: `GET /api/items/32`
- Returns: FoundItem with deadline information

### `collectionExpired`
- `related_id`: **Found Item ID** (e.g., `32`)
- **Navigate to**: `GET /api/items/32`
- Returns: FoundItem (status changed back to FOUND_UNCLAIMED)

### `claimSubmitted`
- `related_id`: **Claim ID** (e.g., `15`)
- **Navigate to**: `GET /api/items/{found_item_id}` (you need to get the found_item_id from the claim)

**Note**: For `claimSubmitted`, the `related_id` is the **Claim ID**, not the Item ID. You may need to:
1. First call `GET /api/items/{id}` to get the item (if you know the item ID)
2. Or get the claim details from the item's claims array

## API Endpoint Reference

### Get Item Details (Correct for notifications)
```
GET /api/items/{id}
```

**Response includes:**
- Item details (title, description, status, location, etc.)
- **Claims array** - All claims for this item, including:
  - Claim status (PENDING, APPROVED, REJECTED)
  - Claim message
  - Approval/rejection details
  - Claimant information
- Collection deadline
- Collection notes
- Transition history

### Submit Claim (NOT for navigation)
```
POST /api/items/{id}/claim
```
This endpoint is **only** for submitting new claims, not getting claim details.

## Flutter Code Fix Example

**Current (Wrong):**
```dart
// ❌ WRONG - This endpoint doesn't support GET
final response = await dio.get('/api/items/${notification.relatedId}/claim');
```

**Correct:**
```dart
// ✅ CORRECT - Get item details which includes claims
final response = await dio.get('/api/items/${notification.relatedId}');

// The response will include:
// - item details
// - claims array (with the user's claim)
// - collection deadline
// - etc.
```

## Testing

After fixing the navigation:

1. Click a `claimApproved` notification
2. Should call: `GET /api/items/32` ✅
3. Should NOT call: `GET /api/items/32/claim` ❌
4. Should display item details with claim information
5. Should show pickup instructions prominently

## Summary

**Rule of thumb for notification navigation:**
- If `related_id` is a **Found Item ID** → Use `GET /api/items/{related_id}`
- If `related_id` is a **Claim ID** → First get the item (or use item's claims array)
- **Never** use `GET /api/items/{id}/claim` - that endpoint is POST only

The `/api/items/{id}` endpoint returns everything needed, including the user's claim details in the `claims` array.








