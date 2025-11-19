# Context Flow Diagram (Level 0 Data Flow Diagram)
## NavistFind AI-Powered Lost & Found and AR Navigation System

---

## Overview

The Context Flow Diagram (Level 0 DFD) represents the NavistFind system as a single process with all external entities and data flows. This diagram shows the system boundaries and how the system interacts with users, administrators, and external services.

---

## Diagram

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                                                                                   │
│                          EXTERNAL ENTITIES                                        │
│                                                                                   │
│  ┌──────────────┐         ┌──────────────┐         ┌──────────────┐         │
│  │    User       │         │    Admin      │         │  External    │         │
│  │  (Student/    │         │  (Staff/      │         │  Services    │         │
│  │   Faculty)    │         │   Personnel)  │         │              │         │
│  │               │         │               │         │              │         │
│  │  Mobile App   │         │  Web Dashboard│         │  • FastAPI   │         │
│  │  (Flutter)    │         │  (Laravel)    │         │    AI Service│         │
│  │               │         │               │         │  • Firebase  │         │
│  └───────┬───────┘         └───────┬───────┘         │    FCM       │         │
│          │                          │                  │  • Google    │         │
│          │                          │                  │    Maps API │         │
│          │                          │                  │  • Email    │         │
│          │                          │                  │    Server   │         │
│          │                          │                  │  • MySQL    │         │
│          │                          │                  │    Database │         │
│          │                          │                  │              │         │
│          │                          │                  └───────┬───────┘         │
│          │                          │                          │                 │
│          │                          │                          │                 │
│          │                          │                          │                 │
│          │                          │                          │                 │
│          │                          │                          │                 │
│          │                          │                          │                 │
│          └──────────────────────────┴──────────────────────────┘                 │
│                                     │                                             │
│                                     │                                             │
│                                     ▼                                             │
│                    ┌─────────────────────────────────────────────────┐           │
│                    │                                                 │           │
│                    │   AI-POWERED LOST & FOUND AND AR NAVIGATION    │           │
│                    │              SYSTEM (NavistFind)                │           │
│                    │                                                 │           │
│                    │  • Lost Item Management                         │           │
│                    │  • Found Item Management                        │           │
│                    │  • AI-Powered Matching (SBERT)                  │           │
│                    │  • Claim Processing & Approval                  │           │
│                    │  • Notification Delivery (Push + Email)          │           │
│                    │  • AR Navigation & Campus Maps                  │           │
│                    │  • User Authentication & Authorization           │           │
│                    │  • Analytics & Reporting                        │           │
│                    │                                                 │           │
│                    └───────┬──────────────┬──────────────┬────────────┘           │
│                            │              │              │                       │
│                            │              │              │                       │
│         ┌──────────────────┘              │              └──────────────────┐   │
│         │                                 │                                 │   │
│         ▼                                 ▼                                 ▼   │
│  ┌──────────────┐              ┌──────────────┐              ┌──────────────┐ │
│  │ FastAPI      │              │ Firebase     │              │ Google Maps │ │
│  │ AI Service   │              │ Cloud        │              │ API         │ │
│  │ (SBERT)      │              │ Messaging    │              │             │ │
│  └──────┬───────┘              └──────┬───────┘              └──────┬───────┘ │
│         │                             │                            │         │
│         │ Item Descriptions           │ Push Notifications          │ Directions│
│         │ (title, description,        │ (title, body, data)        │ Geocoding│
│         │  location, category)        │                            │ Routes   │
│         │                             │                            │ POI Data │
│         │                             │                            │          │
│         │ Matched Items               │ Device Tokens              │ Location │
│         │ (similarity scores)          │ (registration)              │ Queries  │
│         │                             │                            │          │
│         └─────────────────────────────┴────────────────────────────┘          │
│                                    │                                            │
│                                    │                                            │
│                                    ▼                                            │
│                          ┌──────────────┐                                      │
│                          │ Email Server │                                      │
│                          │ (SMTP)       │                                      │
│                          └──────┬───────┘                                      │
│                                 │                                              │
│                                 │ Email Notifications                          │
│                                 │ (claim approvals, reminders)                 │
│                                 │                                              │
│                                 │                                              │
│                                 ▼                                              │
│                          ┌──────────────┐                                      │
│                          │ MySQL         │                                      │
│                          │ Database      │                                      │
│                          │ (Hostinger)   │                                      │
│                          └──────┬───────┘                                      │
│                                 │                                              │
│                                 │ Data Storage/Retrieval                       │
│                                 │ (items, users, matches,                      │
│                                 │  claims, notifications,                     │
│                                 │  device_tokens, ar_locations)              │
│                                 │                                              │
└─────────────────────────────────┴──────────────────────────────────────────────┘
```

---

## Data Flow Descriptions

### Input Flows (External Entities → System)

#### **From User to System**

1. **Lost Item Details**
   - Title, description, category
   - Image file (photo of lost item)
   - Location (where item was lost)
   - Date lost
   - **Flow**: `POST /api/items` with `type: "lost"`

2. **Found Item Details**
   - Title, description, category
   - Image file (photo of found item)
   - Location (where item was found)
   - Date found
   - **Flow**: `POST /api/items` with `type: "found"`

3. **Search / Filter Requests**
   - Keyword search (title, description)
   - Category filter
   - Date range filter
   - Type filter (lost/found)
   - **Flow**: `GET /api/items?query=...&category=...&dateFrom=...`

4. **Match Computation Requests**
   - Request AI matching for specific item
   - **Flow**: `POST /api/items/{id}/compute-matches`

5. **Claim Submissions**
   - Claim message
   - Claimant contact information (name, phone/email)
   - Matched lost item ID (if applicable)
   - **Flow**: `POST /api/items/{id}/claim`

6. **AR Navigation Destination**
   - Selected POI (Point of Interest) ID
   - Current GPS location (latitude, longitude)
   - **Flow**: `GET /api/ar/locations` + Google Maps Directions API call

7. **Authentication Credentials**
   - Email and password (login)
   - User registration data (name, email, password)
   - Google OAuth token
   - **Flow**: `POST /api/login`, `POST /api/register`, `POST /api/auth/google`

8. **Device Token Registration**
   - FCM device token
   - Platform (android/ios/web)
   - **Flow**: `POST /api/device-tokens`

9. **Messages for Admin**
   - User feedback or inquiries
   - **Flow**: Via contact form or notification system

#### **From Admin to System**

1. **User Account Information**
   - Create new user accounts
   - Update user details (name, email, role)
   - Delete user accounts
   - **Flow**: `POST /users`, `PUT /users/{id}`, `DELETE /users/{id}`

2. **Found Item Details**
   - Admin can create found items on behalf of users
   - **Flow**: `POST /items` (web dashboard)

3. **Record Updates / Deletions**
   - Update item status
   - Delete items
   - Bulk operations (bulk update, bulk delete)
   - **Flow**: `PUT /items/{id}`, `DELETE /items/{id}`, `POST /items/bulk-update`

4. **Claim Management Actions**
   - Approve claim requests
   - Reject claim requests (with reason)
   - Mark items as collected
   - Cancel claim approvals
   - Send collection reminders
   - **Flow**: `POST /admin/claims/{id}/approve`, `POST /admin/claims/{id}/reject`

5. **Statistical Summary Requests**
   - Dashboard data queries
   - Analytics reports
   - Export requests (CSV, Excel)
   - **Flow**: `GET /dashboard/data`, `GET /dashboard/export`

6. **System Configuration**
   - Category management (create, update, delete)
   - AR location management (POI coordinates)
   - Building management
   - **Flow**: `POST /categories`, `PUT /categories/{id}`, etc.

---

### Output Flows (System → External Entities)

#### **From System to User**

1. **Lost & Found Item List**
   - Paginated list of items
   - Item details (title, description, image, location, date, status)
   - Category information
   - User information (poster name)
   - **Flow**: `GET /api/items` response

2. **Recommended Matches**
   - AI-matched items with similarity scores
   - Top-K matches (default 10)
   - Match details (item info, score percentage)
   - **Flow**: `GET /api/items/{id}/matches`, `GET /api/items/recommended`

3. **Notifications**
   - Push notifications via Firebase Cloud Messaging
   - In-app notifications (stored in database)
   - Email notifications (optional)
   - Notification types:
     - `matchFound`: AI match discovered
     - `claimApproved`: Claim request approved
     - `claimRejected`: Claim request rejected
     - `collectionReminder`: Collection deadline reminder
     - `adminMessage`: Message from admin
   - **Flow**: FCM push + `GET /api/notifications`

4. **2D Campus Map with Building Markers**
   - Google Maps integration
   - AR location markers (POIs)
   - Route polylines (directions)
   - Building information
   - **Flow**: `GET /api/ar/locations` + Google Maps SDK rendering

5. **AR Navigation Overlay**
   - Directional arrows (ARCore/ARKit)
   - Distance and ETA
   - Step-by-step instructions
   - **Flow**: AR session with Google Directions API data

6. **Authentication Tokens**
   - Laravel Sanctum bearer token
   - Token type ("Bearer")
   - **Flow**: `POST /api/login` response

7. **User Profile Data**
   - User information (name, email, role)
   - User's posted items
   - **Flow**: `GET /api/user`, `GET /api/me/items`

#### **From System to Admin**

1. **User Account List**
   - All registered users
   - User details (name, email, role, created_at)
   - User statistics (items posted, claims made)
   - **Flow**: `GET /users` (web dashboard)

2. **User Messages**
   - Messages/feedback from users
   - Contact inquiries
   - **Flow**: Admin dashboard notifications

3. **Found Item Records**
   - All found items with status
   - Claim information
   - Collection deadlines
   - **Flow**: `GET /item` (web dashboard)

4. **Statistical Summaries**
   - Dashboard analytics:
     - Total items (lost/found)
     - Active claims
     - Match statistics
     - User activity
     - Collection deadlines
     - SLA breaches
   - Chart data (time-series, category distribution)
   - Exportable reports (CSV, Excel)
   - **Flow**: `GET /dashboard/data`, `GET /dashboard/chart-data`, `GET /dashboard/export`

5. **System Status**
   - AI service health
   - Queue status
   - Database connection status
   - **Flow**: `GET /api/ai/health`, system monitoring

6. **Match Queue**
   - Pending AI matches
   - Match quality scores
   - **Flow**: `GET /admin/matches`

7. **Claim Queue**
   - Pending claim requests
   - Claim details (claimant, item, message)
   - **Flow**: `GET /admin/claims`

---

### System ↔ External Services Data Flows

#### **System ↔ FastAPI AI Service**

**To AI Service**:
- **Item Descriptions**: Reference item + candidate items array
  - Format: `{reference_item: {...}, candidate_items: [...], top_k: 10, threshold: 0.6}`
  - Item fields: `id`, `title`, `description`, `location`, `category_id`
  - **Flow**: `POST /v1/match-items` (via `AIService::matchLostAndFound()`)

**From AI Service**:
- **Matched Items**: Array of matched items with similarity scores
  - Format: `{matched_items: [{id: int, score: float}, ...]}`
  - Scores range: 0.0 to 1.0 (cosine similarity)
  - Filtered by threshold (default 0.6)
  - **Flow**: HTTP response from FastAPI

**Health Check**:
- **To AI**: `GET /v1/health`
- **From AI**: `{status: "ok", model_dir: "...", embedding_dim: 384}`

---

#### **System → Firebase Cloud Messaging (FCM)**

**To FCM**:
- **Push Notification Payload**:
  ```json
  {
    "registration_ids": ["token1", "token2", ...],
    "notification": {
      "title": "Match Found!",
      "body": "A found item matches your lost item..."
    },
    "data": {
      "type": "matchFound",
      "related_id": "123",
      "score": "87.5",
      "notification_id": "456"
    }
  }
  ```
  - **Flow**: `POST https://fcm.googleapis.com/fcm/send` (via `FcmService::sendToTokens()`)

**Device Token Management**:
- Register tokens: `POST /api/device-tokens`
- Unregister tokens: `DELETE /api/device-tokens`

---

#### **System ↔ Google Maps API**

**To Google Maps**:
- **Directions Request**:
  - Origin: Current GPS location (lat, lng)
  - Destination: POI coordinates (lat, lng)
  - Mode: `walking`
  - **Flow**: `GET https://maps.googleapis.com/maps/api/directions/json`

- **Geocoding Request**:
  - Address or building name
  - **Flow**: `GET https://maps.googleapis.com/maps/api/geocode/json`

**From Google Maps**:
- **Directions Response**:
  - Routes with polylines
  - Step-by-step instructions
  - Distance and duration
  - **Flow**: JSON response parsed by Flutter app

- **Geocoding Response**:
  - Latitude/longitude coordinates
  - Formatted address
  - **Flow**: JSON response used for location lookup

---

#### **System → Email Server (SMTP)**

**To Email Server**:
- **Email Notifications**:
  - **Claim Approval Email**: Notifies user that claim was approved
  - **Claim Rejection Email**: Notifies user that claim was rejected (with reason)
  - **Collection Reminder Email**: Reminds user of collection deadline
  - **Match Notification Email**: Optional email for AI matches
  - **Flow**: SMTP via Laravel Mail (`Mail::to()->send()`)

**Email Format**:
- Subject: Notification title
- Body: HTML email with notification details
- **Flow**: `NotificationMail` mailable class

---

#### **System ↔ MySQL Database**

**To Database** (INSERT, UPDATE, DELETE):
- **Items**: `lost_items`, `found_items` tables
- **Matches**: `matches` table (AI-generated)
- **Claims**: `claimed_items` table
- **Users**: `users` table
- **Notifications**: `notifications` table
- **Device Tokens**: `device_tokens` table
- **AR Locations**: `ar_locations` table
- **Activity Logs**: `activity_logs` table
- **Categories**: `categories` table
- **Buildings**: `buildings` table

**From Database** (SELECT queries):
- All data retrieval operations
- Paginated queries for listings
- Filtered searches (category, date, keyword)
- Aggregated statistics (dashboard analytics)
- Relationship queries (with eager loading)

**Flow**: Laravel Eloquent ORM → MySQL PDO

---

## Key Characteristics

### System Boundary
- **Single Integrated System**: NavistFind is represented as one cohesive system
- **Internal Components**: Laravel backend, Flutter frontend, queue workers, schedulers
- **External Services**: FastAPI AI, FCM, Google Maps, Email, Database (shown as external for clarity)

### External Entities
1. **User** (Student/Faculty)
   - Primary users of mobile app
   - Create lost/found items, search, claim, navigate

2. **Admin** (Staff/Personnel)
   - Administrative users of web dashboard
   - Manage users, approve claims, view analytics

3. **External Services**
   - FastAPI AI Service (SBERT matching)
   - Firebase Cloud Messaging (push notifications)
   - Google Maps API (navigation, geocoding)
   - Email Server (SMTP notifications)
   - MySQL Database (data persistence)

### Data Flow Types
- **Bidirectional**: Most entities have both input and output flows
- **Unidirectional**: Some flows are one-way (e.g., System → Email Server)
- **Real-time**: Push notifications, AR navigation updates
- **Batch**: Queue jobs, scheduled tasks

### Data Stores
- **MySQL Database**: Primary data store (physically on Hostinger, logically external)
- **File Storage**: Item images, logs (shown as part of system)

---

## Use Cases Represented

### User Use Cases
1. **Report Lost Item**: User → System (Lost Item Details)
2. **Report Found Item**: User → System (Found Item Details)
3. **Search Items**: User → System (Search/Filter Requests)
4. **View Matches**: System → User (Recommended Matches)
5. **Claim Item**: User → System (Claim Submissions)
6. **Navigate to Location**: User → System (AR Navigation Destination)
7. **Receive Notifications**: System → User (Notifications)

### Admin Use Cases
1. **Manage Users**: Admin → System (User Account Information)
2. **Approve/Reject Claims**: Admin → System (Claim Management Actions)
3. **View Analytics**: Admin → System (Statistical Summary Requests)
4. **Manage Items**: Admin → System (Record Updates/Deletions)

### System Use Cases
1. **AI Matching**: System ↔ FastAPI (Item Descriptions ↔ Matched Items)
2. **Send Notifications**: System → FCM (Push Notifications)
3. **Email Notifications**: System → Email Server (Email Notifications)
4. **Store Data**: System → MySQL (Data Storage)
5. **Retrieve Data**: System ← MySQL (Data Retrieval)
6. **Get Directions**: System ↔ Google Maps (Location Queries ↔ Directions Data)

---

## Diagram Notation

- **Rounded Rectangles**: External entities (User, Admin, External Services)
- **Large Rounded Rectangle**: System boundary (NavistFind System)
- **Arrows**: Data flows (labeled with data description)
- **Direction**: Arrow direction indicates data flow direction
- **Labels**: Data flow labels describe what data is being transferred

---

## Related Diagrams

- **Level 1 DFD**: Decomposes the system into major processes (Item Management, AI Matching, Claim Processing, etc.)
- **System Architecture Diagram**: Shows technical components and deployment structure
- **Database ERD**: Shows entity relationships and table structure
- **Sequence Diagrams**: Show detailed interaction flows for specific use cases

---

**Document Version**: 1.0  
**Last Updated**: 2025-01-XX  
**Related Documents**: `SYSTEM_ARCHITECTURE_OVERVIEW.md`








