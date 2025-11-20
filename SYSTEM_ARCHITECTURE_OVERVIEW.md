# System Architecture Overview
## NavistFind Campus Navigation & Lost & Found System

---

## 1. High-Level Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────────────┐
│                           USER LAYER                                     │
├─────────────────────────────────────────────────────────────────────────┤
│  ┌──────────────┐         ┌──────────────┐         ┌──────────────┐   │
│  │   Students   │         │   Staff/     │         │  Admin/      │   │
│  │   (Mobile)   │         │   Faculty    │         │  Personnel   │   │
│  │              │         │   (Mobile)   │         │  (Web)       │   │
│  └──────┬───────┘         └──────┬───────┘         └──────┬───────┘   │
│         │                        │                        │           │
│         │ HTTPS/API              │ HTTPS/API              │ HTTPS     │
│         │ Bearer Token            │ Bearer Token           │ Session   │
└─────────┼────────────────────────┼────────────────────────┼───────────┘
          │                        │                        │
          ▼                        ▼                        ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                    HOSTINGER CLOUD SERVER                                │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                           │
│  ┌────────────────────────┐         ┌────────────────────────┐         │
│  │  Mobile Frontend       │         │  Web Dashboard         │         │
│  │  (Flutter)             │         │  (Laravel Blade)       │         │
│  │                        │         │                        │         │
│  │  • Lost/Found Posting  │         │  • Admin Dashboard    │         │
│  │  • AI Match UI         │         │  • Claim Management   │         │
│  │  • AR Navigation       │         │  • Analytics           │         │
│  │  • Camera/Image Upload│         │  • User Management     │         │
│  └───────────┬────────────┘         └───────────┬────────────┘         │
│              │                                  │                       │
│              │ API Calls                        │ API Calls             │
│              │ (Sanctum Token)                  │ (Session Auth)       │
│              ▼                                  ▼                       │
│  ┌──────────────────────────────────────────────────────────┐          │
│  │         Backend API (Laravel 12)                         │          │
│  │                                                            │          │
│  │  • REST API Routes (/api/*)                              │          │
│  │  • Authentication (Sanctum + JWT)                        │          │
│  │  • Controllers (Item, Auth, Claim, Notification)          │          │
│  │  • Services (AI, FCM, Domain Events)                      │          │
│  │  • Queue Jobs (ComputeMatches, SendNotification)         │          │
│  └───────────┬──────────────────────────────────────────────┘          │
│              │                                                          │
│              │ HTTP POST /v1/match-items                                │
│              │ Bearer Token                                             │
│              ▼                                                          │
│  ┌──────────────────────────────────────────────────────────┐          │
│  │      FastAPI AI Service (SBERT)                          │          │
│  │      (Deployed: Local/VPS/Subdomain)                    │          │
│  │                                                            │          │
│  │  • SentenceTransformer Model                              │          │
│  │  • Vector Embeddings                                      │          │
│  │  • Cosine Similarity Matching                            │          │
│  │  • /v1/match-items, /v1/match-items/best                 │          │
│  └──────────────────────────────────────────────────────────┘          │
│                                                                           │
│  ┌──────────────────────────────────────────────────────────┐          │
│  │         External Services                                 │          │
│  │                                                            │          │
│  │  • Firebase Cloud Messaging (Push Notifications)          │          │
│  │  • Google Maps API (Directions, Geocoding)                │          │
│  │  • SMTP Mail Server (Email Notifications)                 │          │
│  └──────────────────────────────────────────────────────────┘          │
└───────────┬───────────────────────────────────────────────────────────┘
            │
            │ Data Storage / Queries
            ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                           DATA LAYER                                     │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                           │
│  ┌──────────────────────────────────────────────────────────┐          │
│  │         MySQL Database (Hostinger)                        │          │
│  │                                                            │          │
│  │  Core Tables:                                             │          │
│  │  • users (id, name, email, role, password)               │          │
│  │  • lost_items (id, user_id, category_id, title, desc,    │          │
│  │              image_path, location, date_lost, status)     │          │
│  │  • found_items (id, user_id, category_id, title, desc,  │          │
│  │              image_path, location, date_found, status,   │          │
│  │              claimed_by, collection_deadline, etc.)       │          │
│  │  • matches (id, lost_id, found_id, similarity_score)     │          │
│  │  • claimed_items (id, found_item_id, claimant_id,      │          │
│  │              status, approved_by, rejected_by)            │          │
│  │  • notifications (id, user_id, title, body, type,       │          │
│  │              related_id, is_read)                        │          │
│  │  • device_tokens (id, user_id, token, platform)         │          │
│  │  • ar_locations (id, name, latitude, longitude, building)│          │
│  │  • categories, buildings, activity_logs, etc.           │          │
│  └──────────────────────────────────────────────────────────┘          │
│                                                                           │
│  ┌──────────────────────────────────────────────────────────┐          │
│  │         File Storage                                       │          │
│  │  • Item Images (storage/app/public/images/)               │          │
│  │  • Logs (storage/logs/)                                   │          │
│  └──────────────────────────────────────────────────────────┘          │
└─────────────────────────────────────────────────────────────────────────┘
```

### Data Flow Summary

1. **User → Frontend**: Students/staff use Flutter app or web dashboard
2. **Frontend → Laravel API**: Authenticated requests with Sanctum tokens
3. **Laravel → FastAPI AI**: Item descriptions sent for similarity matching
4. **FastAPI → Laravel**: Returns matched items with similarity scores
5. **Laravel → MySQL**: Stores items, matches, claims, notifications
6. **Laravel → FCM/Email**: Sends push notifications and emails
7. **Laravel → Google Maps**: AR navigation routes and geocoding

---

## 2. Component Breakdown

### 2.1 Flutter Mobile App (User Side)

**Location**: `C:\FINAL CAPSTONE PROJECT\navistfind`

**Key Features**:
- **Lost & Found Posting**: Users create lost/found items with images, descriptions, location
- **AI Match Fetching**: Displays recommended matches from FastAPI service
- **AR Navigation**: Google Maps integration with ARCore for campus navigation
- **Authentication**: Token-based auth using Laravel Sanctum
- **Local Caching**: Secure storage for tokens, offline item browsing
- **Camera & File Upload**: Image picker for item photos
- **Push Notifications**: Firebase Cloud Messaging integration

**Key Dependencies** (from `pubspec.yaml`):
- `dio: ^5.8.0` - HTTP client for API calls
- `flutter_secure_storage: ^9.2.4` - Secure token storage
- `google_maps_flutter: ^2.12.1` - Maps integration
- `geolocator: ^11.0.0` - GPS location services
- `camera: ^0.11.1` - Camera for AR navigation
- `firebase_messaging: ^15.1.3` - Push notifications
- `google_sign_in: ^6.2.1` - Google OAuth

**Architecture Pattern**: Riverpod state management (`flutter_riverpod: ^2.6.1`)

### 2.2 Laravel Backend (Hosted on Hostinger)

**Location**: `C:\CAPSTONE PROJECT\campus-nav`

**Core Modules**:

#### **Controllers** (`app/Http/Controllers/`)
- `Api/ItemController.php` - CRUD for lost/found items, matches, claims
- `Api/AuthController.php` - Registration, login, Google OAuth, password reset
- `Api/RecommendationController.php` - Personalized AI recommendations
- `Api/NotificationController.php` - Notification management
- `Api/DeviceTokenController.php` - FCM token registration
- `Api/AIController.php` - AI service health checks
- `ClaimsController.php` - Admin claim approval/rejection
- `DashboardController.php` - Analytics and statistics

#### **Models** (`app/Models/`)
- `LostItem.php` - Lost item entity with status enum
- `FoundItem.php` - Found item with claim workflow
- `ItemMatch.php` - AI-generated matches (lost_id, found_id, similarity_score)
- `ClaimedItem.php` - Claim requests with approval workflow
- `User.php` - User authentication and roles (student, staff, admin)
- `AppNotification.php` - In-app notifications
- `DeviceToken.php` - FCM device tokens
- `ArLocation.php` - AR navigation POIs

#### **Services** (`app/Services/`)
- `AIService.php` - HTTP client for FastAPI integration
- `FcmService.php` - Firebase Cloud Messaging push notifications
- `DomainEventService.php` - Event-driven architecture support

#### **Jobs** (`app/Jobs/`)
- `ComputeItemMatches.php` - Background job for AI matching
- `SendNotificationJob.php` - Queued notification delivery (FCM + Email)
- `MonitorPendingClaimsSlaJob.php` - SLA monitoring for claims
- `SendCollectionReminderJob.php` - Collection deadline reminders

#### **Middleware**
- `ApiAuthMiddleware.php` - API authentication error handling
- `RoleMiddleware.php` - Role-based access control (admin, staff, student)

#### **Authentication**
- **Primary**: Laravel Sanctum (Bearer tokens for API)
- **Secondary**: JWT (configured but not primary)
- **Web**: Session-based authentication for admin dashboard

### 2.3 FastAPI AI Service (SBERT Similarity Engine)

**Location**: `C:\CAPSTONE PROJECT\navistfind-ai-service`

**Technology Stack**:
- FastAPI 0.115.5
- SentenceTransformers 3.0.1 (SBERT model)
- NumPy 2.1.3
- Uvicorn (ASGI server)

**Endpoints**:
- `GET /v1/health` - Service health check
- `POST /v1/match-items` - Returns top-K matches with similarity scores
- `POST /v1/match-items/best` - Returns top 2 matches (highest_best, lower_best)

**Workflow**:
1. Receives reference item (lost/found) + candidate items array
2. Builds text representation: `title | description | location | category_id`
3. Generates vector embeddings using SentenceTransformer
4. Computes cosine similarity between reference and candidates
5. Filters by threshold (default 0.6), sorts by score, returns top-K

**Model**: Custom fine-tuned SBERT model stored in `models/sbert_lost_found_model/`

**Security**: Bearer token authentication (optional, configurable via `AI_SERVICE_API_KEY`)

---

## 3. Database Architecture

### 3.1 Core Tables

#### **users**
```sql
- id (PK)
- name, email, password
- role (enum: student, staff, admin)
- google_id, google_email (for OAuth)
- created_at, updated_at
```

#### **lost_items**
```sql
- id (PK)
- user_id (FK → users)
- category_id (FK → categories)
- title, description, image_path
- location (string)
- date_lost (date)
- status (enum: open, matched, closed) → LostItemStatus enum
- created_at, updated_at
- Indexes: user_id, category_id, status, date_lost, created_at
- Full-text: title, description
```

#### **found_items**
```sql
- id (PK)
- user_id (FK → users, nullable)
- category_id (FK → categories)
- title, description, image_path
- location (string)
- date_found (date)
- status (enum: unclaimed, matched, returned, claim_pending, claim_approved, collected) → FoundItemStatus enum
- claimed_by (FK → users, nullable)
- claimed_at, approved_at, rejected_at, collected_at
- collection_deadline, last_collection_reminder_at
- collection_reminder_stage, collection_notes
- created_at, updated_at
- Indexes: user_id, category_id, status, date_found, created_at
- Full-text: title, description
```

#### **matches**
```sql
- id (PK)
- lost_id (FK → lost_items)
- found_id (FK → found_items)
- similarity_score (float, 0.0-1.0)
- status (enum: pending, confirmed, rejected)
- created_at, updated_at
- Unique constraint: (lost_id, found_id)
- Indexes: lost_id, found_id, status
```

#### **claimed_items**
```sql
- id (PK)
- found_item_id (FK → found_items)
- claimant_id (FK → users)
- matched_lost_item_id (FK → lost_items, nullable)
- message, claimant_contact_name, claimant_contact_info
- status (enum: pending, approved, rejected, withdrawn) → ClaimStatus enum
- approved_by, rejected_by (FK → users, nullable)
- approved_at, rejected_at, review_notes
- created_at, updated_at
- Index: (found_item_id, status)
```

#### **notifications**
```sql
- id (PK)
- user_id (FK → users)
- title, message (body)
- type (enum: AI_MATCH, ADMIN_ALERT, SYSTEM, matchFound, claimApproved, etc.)
- related_id (nullable, references item/claim ID)
- score (nullable, for AI match notifications)
- is_read (boolean)
- created_at, updated_at
- Indexes: user_id, type, is_read, created_at
```

#### **device_tokens**
```sql
- id (PK)
- user_id (FK → users)
- token (string, max 2048, for FCM tokens)
- platform (enum: android, ios, web)
- last_seen_at
- Unique constraint: (user_id, token)
```

#### **ar_locations**
```sql
- id (PK)
- name, building_code, building_id (FK → buildings)
- latitude, longitude (decimal 10,7)
- description, image_path
- created_at, updated_at
- Unique: building_code
- Index: (latitude, longitude)
```

### 3.2 Entity Relationships (ERD)

```
users (1) ──< (N) lost_items
users (1) ──< (N) found_items
users (1) ──< (N) claimed_items (as claimant)
users (1) ──< (N) notifications
users (1) ──< (N) device_tokens

categories (1) ──< (N) lost_items
categories (1) ──< (N) found_items

lost_items (1) ──< (N) matches
found_items (1) ──< (N) matches

found_items (1) ──< (N) claimed_items
lost_items (1) ──< (N) claimed_items (matched_lost_item_id)

buildings (1) ──< (N) ar_locations
```

### 3.3 Data Flow

1. **Lost Item Created** → `lost_items` table
2. **Found Item Created** → `found_items` table
3. **AI Matching** → `matches` table (with similarity_score)
4. **User Claims Found Item** → `claimed_items` table (status: pending)
5. **Admin Approves Claim** → `claimed_items.status = approved`, `found_items.status = claim_approved`
6. **Notification Sent** → `notifications` table + FCM push + Email
7. **Item Collected** → `found_items.status = collected`, `collected_at` timestamp

---

## 4. API Architecture (Flutter ↔ Laravel)

### 4.1 Authentication Endpoints

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/register` | Public | Register new user |
| POST | `/api/login` | Public | Login, returns Sanctum token |
| POST | `/api/auth/google` | Public | Google OAuth sign-in |
| POST | `/api/auth/forgot-password` | Public | Request password reset |
| POST | `/api/auth/reset-password` | Public | Reset password with token |
| GET | `/api/user` | Sanctum | Get authenticated user profile |
| POST | `/api/logout` | Sanctum | Revoke token |

**Request Example (Login)**:
```json
POST /api/login
{
  "email": "user@example.com",
  "password": "SecurePass123!"
}
```

**Response**:
```json
{
  "access_token": "1|abcdefghijklmnopqrstuvwxyz",
  "token_type": "Bearer"
}
```

### 4.2 Item Endpoints

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/items` | Public | List all items (lost/found) |
| GET | `/api/items/{id}` | Public | Get single item details |
| POST | `/api/items` | Sanctum | Create new lost/found item |
| PUT | `/api/items/{id}` | Sanctum | Update item (owner only) |
| DELETE | `/api/items/{id}` | Sanctum | Delete item (owner only) |
| GET | `/api/items/{id}/matches` | Sanctum | Get AI matches for item |
| POST | `/api/items/{id}/compute-matches` | Sanctum (Admin/Staff) | Trigger AI matching job |
| POST | `/api/items/{id}/claim` | Sanctum | Submit claim for found item |
| GET | `/api/items/recommended` | Sanctum | Personalized recommendations |

**Request Example (Create Item)**:
```json
POST /api/items
Authorization: Bearer {token}
{
  "type": "lost",
  "title": "Lost iPhone 14",
  "description": "Black iPhone 14, last seen at library",
  "category_id": 1,
  "location": "Library Building",
  "date_lost": "2025-01-15",
  "image": "base64_encoded_image_or_file"
}
```

**Response Example (Get Matches)**:
```json
GET /api/items/123/matches
[
  {
    "item": {
      "id": 456,
      "title": "Found iPhone",
      "description": "Black iPhone found at library",
      "image_path": "/storage/images/item_456.jpg",
      "status": "unclaimed"
    },
    "score": 0.87
  }
]
```

### 4.3 Notification Endpoints

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/notifications` | Sanctum | List user notifications |
| GET | `/api/notifications/updates` | Sanctum | Get unread notifications |
| POST | `/api/notifications/{id}/read` | Sanctum | Mark notification as read |
| POST | `/api/notifications/mark-all-read` | Sanctum | Mark all as read |
| POST | `/api/device-tokens` | Sanctum | Register FCM device token |
| DELETE | `/api/device-tokens` | Sanctum | Unregister device token |

### 4.4 Admin Endpoints (Web Dashboard)

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/admin/claims` | Session (Admin/Staff) | List all claims |
| POST | `/admin/claims/{id}/approve` | Session (Admin/Staff) | Approve claim |
| POST | `/admin/claims/{id}/reject` | Session (Admin/Staff) | Reject claim |
| POST | `/admin/claims/{id}/mark-collected` | Session (Admin/Staff) | Mark item as collected |
| GET | `/dashboard` | Session (Admin/Staff) | Admin dashboard |
| GET | `/dashboard/data` | Session (Admin/Staff) | Dashboard analytics JSON |

---

## 5. Workflows

### 5.1 AI Integration Workflow (Laravel ↔ FastAPI)

**Sequence Diagram**:
```
User (Flutter)          Laravel API          FastAPI AI Service        MySQL
   │                       │                        │                  │
   │ POST /api/items       │                        │                  │
   │ (Create Lost Item)    │                        │                  │
   ├──────────────────────>│                        │                  │
   │                       │ INSERT lost_items      │                  │
   │                       ├──────────────────────────────────────────>│
   │                       │<─────────────────────────────────────────┤
   │                       │                        │                  │
   │                       │ POST /items/{id}/compute-matches          │
   │                       ├───────────────────────>│                  │
   │                       │                        │                  │
   │                       │                        │ Load SBERT Model │
   │                       │                        │ (in memory)      │
   │                       │                        │                  │
   │                       │ GET Found Items (unclaimed)               │
   │                       ├──────────────────────────────────────────>│
   │                       │<─────────────────────────────────────────┤
   │                       │                        │                  │
   │                       │ POST /v1/match-items   │                  │
   │                       │ {reference_item,       │                  │
   │                       │  candidate_items[]}    │                  │
   │                       ├───────────────────────>│                  │
   │                       │                        │ Build text:      │
   │                       │                        │ "title|desc|loc"  │
   │                       │                        │                  │
   │                       │                        │ Generate         │
   │                       │                        │ embeddings       │
   │                       │                        │                  │
   │                       │                        │ Cosine similarity│
   │                       │                        │ (threshold 0.6)  │
   │                       │                        │                  │
   │                       │ {matched_items: [      │                  │
   │                       │   {id, score}]        │                  │
   │                       │<─────────────────────────┤                  │
   │                       │                        │                  │
   │                       │ INSERT matches         │                  │
   │                       │ (lost_id, found_id,    │                  │
   │                       │  similarity_score)     │                  │
   │                       ├──────────────────────────────────────────>│
   │                       │                        │                  │
   │                       │ Dispatch               │                  │
   │                       │ SendNotificationJob    │                  │
   │                       │                        │                  │
   │                       │ POST FCM API           │                  │
   │                       │ (Push notification)     │                  │
   │                       │                        │                  │
   │  Notification         │                        │                  │
   │  Received (Push)      │                        │                  │
   │<──────────────────────┤                        │                  │
```

**Steps**:
1. User creates lost item via Flutter app
2. Laravel stores item in `lost_items` table
3. User/admin triggers match computation (`POST /api/items/{id}/compute-matches`)
4. Laravel queues `ComputeItemMatches` job
5. Job fetches unclaimed found items (limit 200)
6. Job calls `AIService::matchLostAndFound()` which sends HTTP POST to FastAPI
7. FastAPI builds text representation, generates embeddings, computes cosine similarity
8. FastAPI returns top-K matches (score ≥ 0.6 threshold)
9. Laravel stores matches in `matches` table
10. Laravel dispatches `SendNotificationJob` for new matches
11. Notification sent via FCM + Email to lost item owner

### 5.2 Claim Workflow

```
User (Flutter)          Laravel API          MySQL              Admin (Web)
   │                       │                  │                      │
   │ POST /api/items/{id}/claim              │                      │
   │ {message, contact_info}│                  │                      │
   ├───────────────────────>│                  │                      │
   │                       │ INSERT claimed_items                    │
   │                       │ (status: pending)│                      │
   │                       ├─────────────────>│                      │
   │                       │                  │                      │
   │                       │ UPDATE found_items                       │
   │                       │ (status: claim_pending)                  │
   │                       ├─────────────────>│                      │
   │                       │                  │                      │
   │                       │                  │ Notification to Admin│
   │                       │                  │                      │
   │                       │                  │ POST /admin/claims/{id}/approve│
   │                       │                  │<─────────────────────┤
   │                       │                  │                      │
   │                       │ UPDATE claimed_items                     │
   │                       │ (status: approved) │                      │
   │                       │                  │                      │
   │                       │ UPDATE found_items                       │
   │                       │ (status: claim_approved,                │
   │                       │  collection_deadline: +7 days)           │
   │                       ├─────────────────>│                      │
   │                       │                  │                      │
   │ Notification          │                  │                      │
   │ (Claim Approved)       │                  │                      │
   │<──────────────────────┤                  │                      │
   │                       │                  │                      │
   │ (User collects item)  │                  │                      │
   │                       │                  │ POST /admin/claims/{id}/mark-collected│
   │                       │                  │<─────────────────────┤
   │                       │                  │                      │
   │                       │ UPDATE found_items                       │
   │                       │ (status: collected)                     │
   │                       ├─────────────────>│                      │
   │                       │                  │                      │
   │                       │ UPDATE lost_items                        │
   │                       │ (status: resolved)                       │
   │                       ├─────────────────>│                      │
```

### 5.3 AR Navigation Workflow

```
Flutter App              Laravel API          Google Maps API        MySQL
   │                       │                        │                  │
   │ GET /api/ar/locations │                        │                  │
   │ (Fetch POIs)          │                        │                  │
   ├───────────────────────>│                        │                  │
   │                       │ SELECT ar_locations     │                  │
   │                       ├─────────────────────────>│                  │
   │                       │<─────────────────────────┤                  │
   │                       │                        │                  │
   │ [POI List]            │                        │                  │
   │<───────────────────────┤                        │                  │
   │                       │                        │                  │
   │ User selects destination                        │                  │
   │                       │                        │                  │
   │ Get current GPS location│                        │                  │
   │ (geolocator)          │                        │                  │
   │                       │                        │                  │
   │ GET Directions API    │                        │                  │
   │ origin={lat,lng}      │                        │                  │
   │ destination={poi_lat,poi_lng}                   │                  │
   │ mode=walking          │                        │                  │
   │                       ├─────────────────────────>│                  │
   │                       │                        │                  │
   │                       │ {routes: [{             │                  │
   │                       │   legs: [{steps: []}], │                  │
   │                       │   polyline: "..."}]}   │                  │
   │                       │<─────────────────────────┤                  │
   │                       │                        │                  │
   │ [Route Data]          │                        │                  │
   │<───────────────────────┤                        │                  │
   │                       │                        │                  │
   │ Initialize AR Session │                        │                  │
   │ (ARCore/ARKit)        │                        │                  │
   │                       │                        │                  │
   │ Overlay route on camera view                    │                  │
   │ (Directional arrows, distance, ETA)            │                  │
   │                       │                        │                  │
   │ Fallback: Open Google Maps app                  │                  │
   │ (url_launcher)         │                        │                  │
```

**AR Limitations**:
- Requires ARCore (Android 8+) or ARKit (iOS 11+)
- Outdoor navigation only (GPS accuracy ~5-10m)
- No indoor floor-level positioning
- Falls back to 2D map if AR unavailable

---

## 6. Hostinger Deployment Architecture

### 6.1 Laravel Deployment Structure

**File Structure on Hostinger**:
```
public_html/
├── index.php (Laravel entry point)
├── .htaccess (URL rewriting)
├── storage/ (symlinked from ../storage/app/public)
│   └── images/ (item uploads)
├── build/ (Vite assets)
└── ...

../campus-nav/ (project root, outside public_html)
├── app/
├── config/
├── database/
├── routes/
├── storage/
│   ├── app/public/images/ (actual storage)
│   ├── logs/
│   └── framework/
├── .env (production config)
└── vendor/
```

**Environment Configuration** (`.env`):
```env
APP_ENV=production
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=localhost (or Hostinger MySQL host)
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

AI_SERVICE_URL=https://ai-subdomain.your-domain.com (or localhost:8001)
AI_SERVICE_API_KEY=your_api_key
AI_TOP_K=10
AI_THRESHOLD=0.6

FCM_SERVER_KEY=your_firebase_server_key

MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password

QUEUE_CONNECTION=database (or redis if available)
```

**Cron Jobs** (Hostinger Cron):
```bash
# Run queue worker
* * * * * cd /path/to/campus-nav && php artisan schedule:run >> /dev/null 2>&1

# Or manually run queue:work
php artisan queue:work --tries=3 --timeout=90
```

**Laravel Scheduler** (`app/Console/Kernel.php`):
- `MonitorPendingClaimsSlaJob` - Hourly
- `SendCollectionReminderJob` - Every 3 days
- `ProcessOverdueCollectionsJob` - Daily

### 6.2 FastAPI AI Service Deployment

**Deployment Options**:

**Option A: Same Hostinger VPS (Subdomain)**
```
https://ai.your-domain.com → Nginx reverse proxy → localhost:8001
```

**Option B: Separate VPS/Server**
```
https://ai-service.your-domain.com → FastAPI on port 8001
```

**Option C: Local Development**
```
http://localhost:8001 (for testing)
```

**FastAPI Configuration** (`.env`):
```env
MODEL_DIR=models/sbert_lost_found_model
HOST=0.0.0.0
PORT=8001
AI_SERVICE_API_KEY=your_secure_key
DEFAULT_TOP_K=10
DEFAULT_THRESHOLD=0.6
```

**Running FastAPI**:
```bash
# Development
python main.py

# Production (with Gunicorn/Uvicorn)
uvicorn main:app --host 0.0.0.0 --port 8001 --workers 2
```

**Hosting Limitations**:
- Model loading: ~500MB-1GB RAM per worker
- Python 3.8+ required
- Port 8001 must be open (or use reverse proxy)
- Background model stays in memory (no lazy loading)

### 6.3 Flutter App Deployment

**Release Build**:
```bash
flutter build apk --release  # Android
flutter build ios --release   # iOS
```

**API Configuration** (Flutter):
```dart
// lib/core/config.dart
const String baseUrl = 'https://your-domain.com/api';
```

**Google Maps API Key**:
- Configured in `android/app/src/main/AndroidManifest.xml`
- Restricted to app package name + SHA-1 fingerprint

---

## 7. Technologies & Frameworks Used

### 7.1 Flutter Mobile App
- **Framework**: Flutter 3.8.1+
- **State Management**: Riverpod 2.6.1
- **HTTP Client**: Dio 5.8.0
- **Maps**: google_maps_flutter 2.12.1, flutter_map 6.0.0
- **Location**: geolocator 11.0.0
- **AR**: camera 0.11.1, vector_math 2.1.4
- **Storage**: flutter_secure_storage 9.2.4
- **Notifications**: firebase_messaging 15.1.3
- **Auth**: google_sign_in 6.2.1

### 7.2 Laravel Backend
- **Framework**: Laravel 12.0
- **PHP**: 8.2+
- **Authentication**: Laravel Sanctum 4.1, JWT (tymon/jwt-auth 2.2)
- **Database**: MySQL (Hostinger)
- **Queue**: Database driver (or Redis if available)
- **Mail**: SMTP (Hostinger mail server)
- **HTTP Client**: Laravel HTTP Facade (for FastAPI, FCM)

### 7.3 FastAPI AI Service
- **Framework**: FastAPI 0.115.5
- **Python**: 3.8+
- **ML Library**: sentence-transformers 3.0.1
- **Server**: Uvicorn 0.30.6
- **Model**: Custom fine-tuned SBERT model

### 7.4 External Services
- **Firebase Cloud Messaging**: Push notifications
- **Google Maps Platform**: Directions API, Geocoding API, Maps SDK
- **SMTP Mail**: Hostinger mail server

### 7.5 Hosting Stack
- **Web Server**: Apache/Nginx (Hostinger)
- **Database**: MySQL (Hostinger)
- **PHP**: 8.2+ (Hostinger)
- **Python**: 3.8+ (VPS or Hostinger if supported)

---

## 8. Strengths, Weaknesses, and Recommendations

### 8.1 Strengths

✅ **Modular Architecture**: Clear separation between Flutter, Laravel, and FastAPI  
✅ **Scalable AI Integration**: FastAPI can be deployed separately, horizontal scaling possible  
✅ **Comprehensive Notification System**: FCM + Email dual delivery  
✅ **Role-Based Access Control**: Admin, staff, student roles with middleware  
✅ **Queue System**: Background jobs for AI matching and notifications  
✅ **Full-Text Search**: MySQL full-text indexes on item titles/descriptions  
✅ **AR Navigation**: Modern ARCore integration for campus navigation  

### 8.2 Weaknesses & Security Concerns

⚠️ **API Key Exposure**: FastAPI API key in `.env` (ensure `.env` not in git)  
⚠️ **CORS Configuration**: FastAPI allows all origins (`allow_origins=["*"]`) - tighten in production  
⚠️ **Token Storage**: Flutter secure storage is good, but ensure token rotation  
⚠️ **Image Upload Security**: No image validation/sanitization visible - add file type/size checks  
⚠️ **Rate Limiting**: No rate limiting on API endpoints - add throttling  
⚠️ **Database Indexes**: Some queries may be slow without proper indexes  
⚠️ **Queue Worker**: Single queue worker may bottleneck - need multiple workers  

### 8.3 Performance Bottlenecks

🐌 **AI Matching**: Synchronous HTTP calls to FastAPI can timeout (5s default)  
🐌 **Large Candidate Sets**: Fetching 200 candidates for matching may be slow  
🐌 **Image Processing**: No image optimization/thumbnails - large file uploads  
🐌 **N+1 Queries**: Some controllers may have N+1 issues (eager loading needed)  
🐌 **Full-Text Search**: MySQL full-text search limited compared to Elasticsearch  

### 8.4 Recommendations

#### **Immediate Improvements**:

1. **Add Redis Caching**:
   - Cache AI match results for 1 hour
   - Cache user profiles, categories
   - Reduce database load

2. **Implement Rate Limiting**:
   ```php
   // routes/api.php
   Route::middleware(['throttle:60,1'])->group(function () {
       // API routes
   });
   ```

3. **Image Optimization**:
   - Generate thumbnails on upload
   - Compress images before storage
   - Use CDN for image delivery

4. **Queue Workers**:
   - Run multiple `queue:work` processes
   - Use Supervisor to manage workers
   - Consider Redis queue driver

5. **Database Optimization**:
   - Add composite indexes for common queries
   - Partition large tables (notifications, activity_logs)
   - Use database query caching

#### **Medium-Term Enhancements**:

6. **Elasticsearch Integration**:
   - Replace MySQL full-text with Elasticsearch
   - Better search relevance and performance

7. **WebSocket Notifications**:
   - Real-time notifications via Laravel Echo + Pusher
   - Reduce polling overhead

8. **AI Service Improvements**:
   - Batch processing for multiple items
   - Caching embeddings in Redis
   - Model versioning and A/B testing

9. **Monitoring & Logging**:
   - Laravel Telescope for debugging
   - Sentry for error tracking
   - Application performance monitoring (APM)

10. **API Versioning**:
    - Version API endpoints (`/api/v1/`, `/api/v2/`)
    - Backward compatibility

#### **Long-Term Architecture**:

11. **Microservices**:
    - Separate notification service
    - Separate AI service (already done)
    - Separate file storage service

12. **Load Balancing**:
    - Multiple Laravel instances behind load balancer
    - Database read replicas

13. **CDN Integration**:
    - CloudFlare or AWS CloudFront for static assets
    - Image delivery optimization

14. **Database Migration**:
    - Consider PostgreSQL for better JSON/geospatial support
    - Or keep MySQL but optimize queries

---

## 9. Summary

The NavistFind system is a **three-tier architecture** with:
- **Frontend**: Flutter mobile app + Laravel Blade web dashboard
- **Backend**: Laravel 12 REST API with Sanctum authentication
- **AI Service**: FastAPI with SBERT for semantic similarity matching
- **Database**: MySQL on Hostinger
- **External**: Firebase FCM, Google Maps API, SMTP mail

The system handles **lost & found item management** with AI-powered matching, claim workflows, AR navigation, and comprehensive notifications. The architecture is **scalable** but would benefit from caching, rate limiting, and performance optimizations for production scale.

---

**Document Version**: 1.0  
**Last Updated**: 2025-01-XX  
**Author**: System Architecture Analysis









