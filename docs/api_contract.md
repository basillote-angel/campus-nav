# Laravel Backend API Contract

Complete REST API documentation for the NavistFind Campus Navigation System. This document outlines all endpoints, request/response schemas, authentication, and usage examples for the Flutter mobile app.

## Base URL

```
https://your-domain.com/api
```

## Authentication

All protected endpoints require **Laravel Sanctum Bearer Token** authentication.

### Headers

```
Authorization: Bearer {access_token}
Content-Type: application/json
Accept: application/json
```

### Getting an Access Token

See **Authentication Endpoints** section below.

---

## Authentication Endpoints

### Register New User

**Endpoint**: `POST /api/register`

**Request Body**:
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "SecurePass123!@#"
}
```

**Password Requirements**:
- Minimum 8 characters
- At least one uppercase letter
- At least one lowercase letter
- At least one digit
- At least one special character: `!@#$%^&*(),.?":{}|<>`

**Response** (`200 OK`):
```json
{
  "access_token": "1|abcdefghijklmnopqrstuvwxyz",
  "token_type": "Bearer"
}
```

**Error Responses**:
- `422 Unprocessable Entity`: Validation errors (email already exists, password doesn't meet requirements)
- `500 Internal Server Error`: Server error

---

### Login

**Endpoint**: `POST /api/login`

**Request Body**:
```json
{
  "email": "john@example.com",
  "password": "SecurePass123!@#"
}
```

**Response** (`200 OK`):
```json
{
  "access_token": "1|abcdefghijklmnopqrstuvwxyz",
  "token_type": "Bearer"
}
```

**Error Responses**:
- `401 Unauthorized`: Invalid credentials
- `500 Internal Server Error`: Server error

---

### Get User Profile

**Endpoint**: `GET /api/user`

**Authentication**: Required

**Response** (`200 OK`):
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "role": "student",
  "created_at": "2024-01-01T00:00:00.000000Z",
  "updated_at": "2024-01-01T00:00:00.000000Z"
}
```

---

### Get My Profile (Alternative)

**Endpoint**: `GET /api/me`

**Authentication**: Required

**Response** (`200 OK`):
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "role": "student",
  "created_at": "2024-01-01T00:00:00.000000Z",
  "updated_at": "2024-01-01T00:00:00.000000Z"
}
```

**Notes**: 
- Similar to `/api/user`, returns authenticated user profile
- Returns `404 Not Found` if user not found

---

### Logout

**Endpoint**: `POST /api/logout`

**Authentication**: Required

**Response** (`200 OK`):
```json
{
  "message": "Logged out successfully"
}
```

**Note**: This revokes all tokens for the authenticated user.

---

### Forgot Password

**Endpoint**: `POST /api/auth/forgot-password`

**Request Body**:
```json
{
  "email": "john@example.com"
}
```

**Response** (`200 OK`):
```json
{
  "message": "Password reset link has been sent to your email address."
}
```

**Error Responses**:
- `422 Unprocessable Entity`: Email not found
- `500 Internal Server Error`: Server error

---

### Reset Password

**Endpoint**: `POST /api/auth/reset-password`

**Request Body**:
```json
{
  "token": "password_reset_token_from_email",
  "email": "john@example.com",
  "password": "NewSecurePass123!@#",
  "password_confirmation": "NewSecurePass123!@#"
}
```

**Response** (`200 OK`):
```json
{
  "message": "Password has been reset successfully."
}
```

**Error Responses**:
- `400 Bad Request`: Invalid or expired reset token
- `404 Not Found`: User not found
- `422 Unprocessable Entity`: Validation errors (password doesn't meet requirements, confirmation mismatch)
- `500 Internal Server Error`: Server error

**Notes**:
- After successful password reset, all existing tokens are revoked for security
- User will need to login again after resetting password

---

### Google Sign-In

**Endpoint**: `POST /api/auth/google`

**Request Body**:
```json
{
  "id_token": "google_id_token"
}
```

**Request Fields**:
- `id_token` (required): String - Google ID token from Google Sign-In

**Response** (`200 OK`):
```json
{
  "access_token": "1|abcdefghijklmnopqrstuvwxyz",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "student"
  }
}
```

**Error Responses**:
- `400 Bad Request`: Invalid or expired Google ID token
- `422 Unprocessable Entity`: Validation errors (`id_token` required)
- `500 Internal Server Error`: Server error

**Notes**:
- If user with email doesn't exist, a new user account is created
- Google users have their email automatically verified
- Google provider info (`provider`, `provider_id`) is stored for future logins

---

## Item Endpoints

### List Items (Browse)

**Endpoint**: `GET /api/items`

**Authentication**: Optional (public browse, but auth needed for personalized features)

**Query Parameters**:
- `type` (optional): `lost` | `found` - Filter by item type
- `category` (optional): Category ID - Filter by category
- `dateFrom` (optional): Date string (YYYY-MM-DD) - Filter by date range start
- `dateTo` (optional): Date string (YYYY-MM-DD) - Filter by date range end
- `query` (optional): String - Keyword search in title/description
- `sort` (optional): `newest` | `relevance` - Sort order (default: `newest`)
- `perPage` (optional): Integer (1-100, default: 20) - Results per page
- `includeReturned` (optional): Boolean - Include `COLLECTED`/`CLAIM_APPROVED` found items (default: false)

**Response** (`200 OK`):
```json
{
  "data": [
    {
      "id": 1,
      "type": "found",
      "title": "Black Backpack",
      "description": "Found in library",
      "status": "FOUND_UNCLAIMED",
      "category": {
        "id": 1,
        "name": "Bags"
      },
      "location": "Library",
      "date": "2024-01-15",
      "image_path": "/storage/images/item1.jpg",
      "created_at": "2024-01-15T10:00:00.000000Z",
      "updated_at": "2024-01-15T10:00:00.000000Z"
    },
    {
      "id": 2,
      "type": "lost",
      "title": "Lost Wallet",
      "description": "Brown leather wallet",
      "status": "LOST_REPORTED",
      "category": {
        "id": 2,
        "name": "Wallets"
      },
      "location": "Cafeteria",
      "date": "2024-01-14",
      "image_path": null,
      "created_at": "2024-01-14T14:30:00.000000Z",
      "updated_at": "2024-01-14T14:30:00.000000Z"
    }
  ],
  "current_page": 1,
  "last_page": 5,
  "per_page": 20,
  "total": 100
}
```

**Notes**:
- When `type` is not specified, returns merged list of lost + found items
- Found items with status `COLLECTED` or `CLAIM_APPROVED` are excluded by default unless `includeReturned=true`
- Students should typically use `includeReturned=false` to see only available items

---

### Get Item Details

**Endpoint**: `GET /api/items/{id}`

**Authentication**: Optional

**Response** (`200 OK`):

For **Found Items**:
```json
{
  "id": 1,
  "title": "Black Backpack",
  "description": "Found in library",
  "status": "FOUND_UNCLAIMED",
  "category": {
    "id": 1,
    "name": "Bags"
  },
  "location": "Library",
  "date_found": "2024-01-15",
  "image_path": "/storage/images/item1.jpg",
  "collection_deadline": null,
  "collection_notes": null,
  "claim_status_summary": {
    "pending": 2,
    "approved": 0
  },
  "transition_history": [
    {
      "status": "FOUND_UNCLAIMED",
      "occurred_at": "2024-01-15T10:00:00.000000Z"
    }
  ],
  "claims": [
    {
      "id": 1,
      "found_item_id": 1,
      "claimant": {
        "id": 5,
        "name": "Jane Smith",
        "email": "jane@example.com"
      },
      "message": "I believe this is my backpack",
      "status": "PENDING",
      "matched_lost_item_id": null,
      "contact": {
        "name": "Jane Smith",
        "info": "jane@example.com"
      },
      "approved_by": null,
      "approved_at": null,
      "rejected_by": null,
      "rejected_at": null,
      "rejection_reason": null,
      "created_at": "2024-01-16T08:00:00.000000Z",
      "updated_at": "2024-01-16T08:00:00.000000Z"
    }
  ],
  "created_at": "2024-01-15T10:00:00.000000Z",
  "updated_at": "2024-01-15T10:00:00.000000Z"
}
```

For **Lost Items**:
```json
{
  "id": 2,
  "title": "Lost Wallet",
  "description": "Brown leather wallet with ID cards",
  "status": "LOST_REPORTED",
  "category": {
    "id": 2,
    "name": "Wallets"
  },
  "location": "Cafeteria",
  "date_lost": "2024-01-14",
  "image_path": null,
  "transition_history": [
    {
      "status": "LOST_REPORTED",
      "occurred_at": "2024-01-14T14:30:00.000000Z"
    }
  ],
  "created_at": "2024-01-14T14:30:00.000000Z",
  "updated_at": "2024-01-14T14:30:00.000000Z"
}
```

**Error Responses**:
- `404 Not Found`: Item not found

---

### Create Item (Lost or Found)

**Endpoint**: `POST /api/items`

**Authentication**: Required

**Request Body**:
```json
{
  "type": "found",
  "title": "Black Backpack",
  "description": "Found in library, black with red straps",
  "category_id": 1,
  "location": "Library Building A",
  "date": "2024-01-15",
  "image_path": "/storage/images/item1.jpg",
  "include_matches": false
}
```

**Request Fields**:
- `type` (required): `lost` | `found` - Note: Students can only create `lost` items; Admin/Staff can create both
- `title` (required): String
- `description` (required): String
- `category_id` (required): Integer, valid category ID
- `location` (optional): String
- `date` (optional): Date string (YYYY-MM-DD) - Used for `date_lost` (lost items) or `date_found` (found items)
- `image_path` (optional): String - Image path or URL
- `include_matches` (optional): Boolean - Whether to queue AI matching job

**Role Restrictions**:
- Students can only create `lost` items (returns `403` if attempting to create `found` items)
- Admin/Staff can create both `lost` and `found` items

**Response** (`201 Created`):
```json
{
  "id": 1,
  "title": "Black Backpack",
  "description": "Found in library, black with red straps",
  "status": "FOUND_UNCLAIMED",
  "category": {
    "id": 1,
    "name": "Bags"
  },
  "location": "Library Building A",
  "date_found": "2024-01-15",
  "image_path": "/storage/images/item1.jpg",
  "created_at": "2024-01-15T10:00:00.000000Z",
  "updated_at": "2024-01-15T10:00:00.000000Z"
}
```

**Error Responses**:
- `403 Forbidden`: Students attempting to create `found` items (only admin/staff can)
- `422 Unprocessable Entity`: Validation errors
- `401 Unauthorized`: Not authenticated
- `500 Internal Server Error`: Server error

---

### Update Item

**Endpoint**: `PUT /api/items/{id}`

**Authentication**: Required (owner only)

**Request Body**: Same as create, but all fields optional (only send fields to update)

**Request Fields** (all optional):
- `title` (optional): String
- `description` (optional): String
- `category_id` (optional): Integer, valid category ID
- `location` (optional): String
- `date_lost` or `date_found` (optional): Date string (YYYY-MM-DD) - depends on item type
- `status` (optional): Status enum value - **Admin/Staff only** (students cannot change status)
- `type` (optional): `lost` | `found` - **Admin/Staff only** (students cannot set to `found`)
- `collection_deadline` (optional): DateTime string - Only for found items
- `image_path` (optional): String - Image path or URL
- `include_matches` (optional): Boolean - Whether to queue AI matching job after update

**Role Restrictions**:
- Students **cannot** change `status` field (returns `403` if attempted)
- Students **cannot** set `type` to `found` (returns `403` if attempted)
- Only Admin/Staff can modify status and change type
- Owner must be the authenticated user to update item

**Response** (`200 OK`): Same as create response with updated data

**Response** (includes `meta` if `include_matches` was true):
```json
{
  "id": 1,
  "title": "Black Backpack",
  "description": "Updated description",
  "status": "FOUND_UNCLAIMED",
  "category": {
    "id": 1,
    "name": "Bags"
  },
  "location": "Library Building A",
  "date_found": "2024-01-15",
  "image_path": "/storage/images/item1.jpg",
  "created_at": "2024-01-15T10:00:00.000000Z",
  "updated_at": "2024-01-15T11:00:00.000000Z",
  "meta": {
    "matchesQueued": true
  }
}
```

**Error Responses**:
- `403 Forbidden`: 
  - Not the owner
  - Student attempting to change `status` field
  - Student attempting to set `type` to `found`
- `404 Not Found`: Item not found
- `422 Unprocessable Entity`: Validation errors

---

### Delete Item

**Endpoint**: `DELETE /api/items/{id}`

**Authentication**: Required (owner only)

**Query Parameters**:
- `type` (optional): `lost` | `found` - Item type (helps disambiguate if ID conflicts exist)

**Response** (`200 OK`):
```json
{
  "deleted": true
}
```

**Error Responses**:
- `403 Forbidden`: Not the owner
- `404 Not Found`: Item not found

---

### Submit Claim for Found Item

**Endpoint**: `POST /api/items/{id}/claim`

**Authentication**: Required

**Request Body**:
```json
{
  "message": "I believe this is my backpack. It has my student ID inside.",
  "contactName": "John Doe",
  "contactInfo": "john@example.com",
  "matchedLostItemId": 5
}
```

**Request Fields**:
- `message` (required): String, max 2000 chars - Claim evidence/description
- `contactName` (optional): String, max 255 chars - Contact name
- `contactInfo` (optional): String, max 255 chars - Contact details (email/phone)
- `matchedLostItemId` (optional): Integer - Link to lost item report if student posted one

**Response** (`200 OK`):
```json
{
  "id": 1,
  "title": "Black Backpack",
  "description": "Found in library",
  "status": "CLAIM_PENDING",
  "category": {
    "id": 1,
    "name": "Bags"
  },
  "location": "Library",
  "date_found": "2024-01-15",
  "image_path": "/storage/images/item1.jpg",
  "collection_deadline": null,
  "collection_notes": null,
  "claim_status_summary": {
    "pending": 1
  },
  "transition_history": [
    {
      "status": "FOUND_UNCLAIMED",
      "occurred_at": "2024-01-15T10:00:00.000000Z"
    },
    {
      "status": "CLAIM_PENDING",
      "occurred_at": "2024-01-16T08:00:00.000000Z"
    }
  ],
  "claims": [
    {
      "id": 1,
      "found_item_id": 1,
      "claimant": {
        "id": 5,
        "name": "John Doe",
        "email": "john@example.com"
      },
      "message": "I believe this is my backpack. It has my student ID inside.",
      "status": "PENDING",
      "matched_lost_item_id": 5,
      "contact": {
        "name": "John Doe",
        "info": "john@example.com"
      },
      "approved_by": null,
      "approved_at": null,
      "rejected_by": null,
      "rejected_at": null,
      "rejection_reason": null,
      "created_at": "2024-01-16T08:00:00.000000Z",
      "updated_at": "2024-01-16T08:00:00.000000Z"
    }
  ],
  "created_at": "2024-01-15T10:00:00.000000Z",
  "updated_at": "2024-01-16T08:00:00.000000Z",
  "meta": {
    "claimId": 1,
    "message": "Claim submitted. Admin will review shortly.",
    "hasMultipleClaims": false
  }
}
```

**Response Meta Fields**:
- `claimId`: ID of the newly created claim
- `message`: Human-readable success message
- `hasMultipleClaims`: Boolean - true if item already had other pending claims

**Error Responses**:
- `404 Not Found`: Item not found
- `422 Unprocessable Entity`: 
  - Item is not available to claim (status is not `FOUND_UNCLAIMED` or `CLAIM_PENDING`)
  - You already have an active claim for this item
  - Validation errors (message required, etc.)
- `401 Unauthorized`: Not authenticated

**Notes**:
- After successful claim submission, student will receive a `claimSubmitted` push notification
- If item already has pending claims, a `multipleClaims` notification is sent to admins
- If this is the first claim, a `newClaim` notification is sent to admins

---

### Get AI Matches for Item

**Endpoint**: `GET /api/items/{id}/matches`

**Authentication**: Optional

**Response** (`200 OK`):
```json
{
  "data": [
    {
      "id": 10,
      "lost_id": 5,
      "found_id": 1,
      "similarity_score": 0.85,
      "status": "pending",
      "lost_item": {
        "id": 5,
        "title": "Lost Black Backpack",
        "description": "Black backpack with red straps, lost in library",
        "status": "LOST_REPORTED",
        "category": {
          "id": 1,
          "name": "Bags"
        },
        "location": "Library",
        "date_lost": "2024-01-14"
      },
      "found_item": {
        "id": 1,
        "title": "Black Backpack",
        "description": "Found in library",
        "status": "FOUND_UNCLAIMED"
      },
      "created_at": "2024-01-15T12:00:00.000000Z"
    }
  ]
}
```

---

### Get My Items

**Endpoint**: `GET /api/me/items`

**Authentication**: Required

**Response** (`200 OK`):
```json
[
  {
    "id": 1,
    "type": "found",
    "title": "Black Backpack",
    "description": "Found in library",
    "status": "CLAIM_PENDING",
    "category_id": 1,
    "location": "Library",
    "date_found": "2024-01-15",
    "image_path": "/storage/images/item1.jpg",
    "user_id": 5,
    "created_at": "2024-01-15T10:00:00.000000Z",
    "updated_at": "2024-01-16T08:00:00.000000Z"
  },
  {
    "id": 2,
    "type": "lost",
    "title": "Lost Wallet",
    "description": "Brown leather wallet",
    "status": "LOST_REPORTED",
    "category_id": 2,
    "location": "Cafeteria",
    "date_lost": "2024-01-14",
    "image_path": null,
    "user_id": 5,
    "created_at": "2024-01-14T14:30:00.000000Z",
    "updated_at": "2024-01-14T14:30:00.000000Z"
  }
]
```

**Notes**:
- Returns a flat array (not paginated) of all items created by the authenticated user
- Each item includes a `type` field (`"lost"` or `"found"`) to distinguish item types
- Items are sorted by `created_at` descending (newest first)
- Returns empty array `[]` if user has no items or on error

---

### Get Recommended Items

**Endpoint**: `GET /api/items/recommended`

**Authentication**: Required (students only)

**Response** (`200 OK`):
```json
[
  {
    "item": {
      "id": 1,
      "title": "Black Backpack",
      "description": "Found in library",
      "status": "FOUND_UNCLAIMED",
      "category": {
        "id": 1,
        "name": "Bags"
      },
      "location": "Library",
      "date_found": "2024-01-15",
      "image_path": "/storage/images/item1.jpg",
      "created_at": "2024-01-15T10:00:00.000000Z",
      "updated_at": "2024-01-15T10:00:00.000000Z"
    },
    "score": 0.85
  },
  {
    "item": {
      "id": 3,
      "title": "Navy Blue Backpack",
      "description": "Found near cafeteria",
      "status": "FOUND_UNCLAIMED",
      "category": {
        "id": 1,
        "name": "Bags"
      },
      "location": "Cafeteria",
      "date_found": "2024-01-14",
      "image_path": null,
      "created_at": "2024-01-14T09:00:00.000000Z",
      "updated_at": "2024-01-14T09:00:00.000000Z"
    },
    "score": 0.72
  }
]
```

**Notes**:
- Returns AI-recommended found items based on user's active lost item reports
- Only students can access this endpoint (returns empty array for admin/staff)
- Items are ranked by similarity score (highest first)
- Returns empty array `[]` if:
  - User has no active lost items (`LOST_REPORTED` status)
  - No matching found items available
  - User is not a student
  - Error occurs during processing
- Score represents AI similarity match (0.0 to 1.0, higher is better match)

---

### Get AI Service Health

**Endpoint**: `GET /api/ai/health`

**Authentication**: Not required (public endpoint)

**Response** (`200 OK` if healthy):
```json
{
  "ok": true,
  "service": "navistfind-ai-service",
  "timestamp": "2024-01-15T10:00:00.000000Z"
}
```

**Response** (`503 Service Unavailable` if unhealthy):
```json
{
  "ok": false,
  "service": "navistfind-ai-service",
  "error": "Connection timeout",
  "timestamp": "2024-01-15T10:00:00.000000Z"
}
```

**Notes**:
- Health check endpoint for the AI matching service
- Useful for debugging connection issues
- Can be called without authentication

---

## Notification Endpoints

### List Notifications

**Endpoint**: `GET /api/notifications`

**Authentication**: Required

**Query Parameters**:
- `page` (optional): Integer - Page number (default: 1)
- `perPage` (optional): Integer - Results per page (default: 20)

**Response** (`200 OK`):
```json
{
  "data": [
    {
      "id": "1",
      "type": "claimSubmitted",
      "title": "Claim Submitted",
      "body": "Your claim for 'Black Backpack' has been submitted. The admin will review it soon.",
      "related_id": 10,
      "score": null,
      "created_at": "2024-01-16T08:00:00.000Z",
      "read_at": null
    },
    {
      "id": "2",
      "type": "claimApproved",
      "title": "Claim Approved! ✅",
      "body": "Your claim for 'Black Backpack' was approved! ✅\n\n🏢 IMPORTANT: Physical collection required at admin office.\n\n📍 Location: Admin Office\n⏰ Hours: Monday-Friday, 8:00 AM - 5:00 PM\n📅 Collect By: January 23, 2024\n🆔 Required: Bring valid ID (Student ID or Government ID)\n\n📞 Questions? admin@school.edu or (555) 123-4567",
      "related_id": 1,
      "score": null,
      "created_at": "2024-01-17T10:00:00.000Z",
      "read_at": "2024-01-17T10:05:00.000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5
  },
  "unread_count": 3
}
```

---

### Get Notification Updates (Polling)

**Endpoint**: `GET /api/notifications/updates`

**Authentication**: Required

**Response** (`200 OK`):
```json
{
  "unread_count": 3,
  "recent_notifications": [
    {
      "id": "3",
      "type": "collectionReminder",
      "title": "Collection Reminder",
      "body": "Reminder for 'Black Backpack': please collect your item before the deadline.\n\n📍 Location: Admin Office\n⏰ Office Hours: Monday-Friday, 8:00 AM - 5:00 PM\n📅 Deadline: January 23, 2024 5:00 PM\n🆔 Bring a valid ID for verification.\n\n📞 Questions? admin@school.edu / (555) 123-4567",
      "related_id": 1,
      "score": null,
      "created_at": "2024-01-20T09:00:00.000Z",
      "read_at": null
    }
  ],
  "timestamp": "2024-01-20T09:15:00.000Z"
}
```

**Usage**: Poll this endpoint periodically (e.g., every 30-60 seconds) when app is in foreground to get real-time notification updates without waiting for push notifications.

---

### Mark Notification as Read

**Endpoint**: `POST /api/notifications/{id}/read`

**Authentication**: Required

**Response** (`204 No Content`)

**Error Responses**:
- `404 Not Found`: Notification not found or doesn't belong to user

---

### Mark All Notifications as Read

**Endpoint**: `POST /api/notifications/mark-all-read`

**Authentication**: Required

**Response** (`200 OK`):
```json
{
  "message": "All notifications marked as read"
}
```

---

## Device Token Endpoints

### Register Device Token (FCM)

**Endpoint**: `POST /api/device-tokens`

**Authentication**: Required

**Request Body**:
```json
{
  "platform": "android",
  "token": "fcm_device_token_string_here"
}
```

**Request Fields**:
- `platform` (required): `android` | `ios` | `web`
- `token` (required): String, max 2048 chars - FCM registration token

**Response** (`201 Created`):
```json
{
  "ok": true
}
```

**Usage**: Call this endpoint when:
- User logs in
- App starts and FCM token is obtained/refreshed
- FCM token changes (after app reinstall, etc.)

**Notes**:
- If token already exists for a different user, it will be reassigned to the current user
- Token is updated on each call (upsert behavior)

---

### Unregister Device Token

**Endpoint**: `DELETE /api/device-tokens`

**Authentication**: Required

**Request Body**:
```json
{
  "token": "fcm_device_token_string_here"
}
```

**Response** (`204 No Content`)

**Usage**: Call when user logs out or wants to disable push notifications.

---

## Status Values Reference

### Found Item Statuses

- `FOUND_UNCLAIMED`: Item is available for claims
- `CLAIM_PENDING`: One or more claims are pending admin review
- `CLAIM_APPROVED`: A claim was approved; awaiting physical collection
- `COLLECTED`: Item was collected by claimant at admin office

### Claim (ClaimedItem) Statuses

- `PENDING`: Waiting for admin review
- `APPROVED`: Admin approved this claim
- `REJECTED`: Admin rejected this claim or another claimant won

### Lost Item Statuses

- `LOST_REPORTED`: Student reported item as lost
- `RESOLVED`: Found item was collected and linked lost item is resolved

---

## Error Response Format

All error responses follow this structure:

```json
{
  "message": "Error description",
  "errors": {
    "field_name": [
      "Error message for this field"
    ]
  }
}
```

**HTTP Status Codes**:
- `200 OK`: Success
- `201 Created`: Resource created successfully
- `204 No Content`: Success with no response body
- `400 Bad Request`: Invalid request
- `401 Unauthorized`: Authentication required or token invalid
- `403 Forbidden`: Not authorized to perform action
- `404 Not Found`: Resource not found
- `422 Unprocessable Entity`: Validation errors
- `500 Internal Server Error`: Server error

---

## Pagination Format

Paginated responses include:

```json
{
  "data": [...],
  "current_page": 1,
  "last_page": 5,
  "per_page": 20,
  "total": 100,
  "from": 1,
  "to": 20
}
```

---

## Date/Time Formats

- **Dates**: ISO 8601 format (`YYYY-MM-DD`)
- **Timestamps**: ISO 8601 format with timezone (`YYYY-MM-DDTHH:mm:ss.ssssssZ`)
- Example: `2024-01-15T10:00:00.000000Z`

---

## Rate Limiting

API endpoints may be rate-limited. Check response headers:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1640995200
```

---

## Best Practices for Mobile App

1. **Token Management**:
   - Store access token securely (keychain/keystore)
   - Refresh token before expiry (if refresh endpoint exists)
   - Handle token expiration gracefully (redirect to login)

2. **Error Handling**:
   - Always check HTTP status codes
   - Parse error messages and show user-friendly messages
   - Handle network errors and retry with exponential backoff

3. **Notifications**:
   - Register device token on login
   - Unregister on logout
   - Poll `/api/notifications/updates` periodically when app is active
   - Handle push notifications for background/foreground states

4. **Item Filtering**:
   - Use `includeReturned=false` when browsing for available items
   - Filter by `status` when showing user's items (e.g., show only `CLAIM_PENDING` for pending claims)

5. **Pagination**:
   - Implement infinite scroll or "load more" buttons
   - Respect `perPage` limits to avoid loading too much data

6. **Image Handling**:
   - Handle `image_path` URLs (may need to prepend base URL)
   - Implement image caching for better performance
   - Support both file upload and base64 for image submission

---

**Last Updated**: Based on Laravel backend implementation as of latest API endpoints review.

