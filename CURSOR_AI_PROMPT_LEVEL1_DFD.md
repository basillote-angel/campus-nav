# Cursor AI Prompt: Generate Level 1 Data Flow Diagram

## Task Description

Create a comprehensive **Level 1 Data Flow Diagram (DFD)** for the NavistFind AI-Powered Lost & Found and AR Navigation System. The diagram should decompose the system into major functional processes and show all data flows between external entities (Mobile User, Admin User) and internal processes, as well as inter-process data flows.

---

## System Context

The NavistFind system is a three-tier architecture consisting of:
- **Frontend**: Flutter mobile app (for students/staff) + Laravel Blade web dashboard (for admin)
- **Backend**: Laravel 12 REST API with Sanctum authentication
- **AI Service**: FastAPI with SBERT for semantic similarity matching
- **Database**: MySQL on Hostinger
- **External Services**: Firebase FCM, Google Maps API, SMTP mail

---

## External Entities

### 1. User (Mobile User - Student/Faculty)
- Uses Flutter mobile application
- Creates lost/found items
- Searches and filters items
- Submits claims for found items
- Uses AR navigation
- Receives notifications

### 2. Admin (Admin User - Staff/Personnel)
- Uses Laravel web dashboard
- Manages user accounts
- Manages found items
- Approves/rejects claims
- Views analytics and reports
- Monitors system status

---

## Required Processes (8 Major Processes)

The Level 1 DFD must include these 8 processes:

### 1.0 User Management
**Function**: Handles user account operations and authentication
- User registration
- User login (email/password, Google OAuth)
- Password reset
- Profile management
- Role assignment (student, staff, admin)

**Key Data Flows**:
- From Admin: User account information, User account list
- To Admin: User account list
- To System: Authenticated user sessions, User profiles

**Data Store**: `users` table

---

### 2.0 Lost Item Management
**Function**: Manages lost item lifecycle from creation to resolution
- Create lost items
- Update lost items
- Delete lost items
- List lost items
- Status management (open → matched → closed/resolved)

**Key Data Flows**:
- From User: Lost item details, Lost item list
- To User: Lost item list, Lost item details
- To Process 3.0: Item data (for matching)

**Data Store**: `lost_items` table

**Status Flow**: `LOST_REPORTED` → `LOST_MATCHED` → `RESOLVED`

---

### 3.0 Found Item Management
**Function**: Manages found item lifecycle including claim processing
- Create found items
- Update found items
- Delete found items
- List found items
- Status management (unclaimed → claim_pending → claim_approved → collected)
- Collection deadline management
- Collection reminder scheduling

**Key Data Flows**:
- From Admin: Found item detail, Found item records, Record updates/deletions
- From Process 2.0: Item data (for matching)
- To Admin: Found item records
- To Process 4.0: Item records (for AI matching)

**Data Stores**: `found_items` table, `claimed_items` table

**Status Flow**: `FOUND_UNCLAIMED` → `CLAIM_PENDING` → `CLAIM_APPROVED` → `COLLECTED`

---

### 4.0 Item Matching & Notifications
**Function**: AI-powered matching and notification delivery
- Trigger AI matching (calls FastAPI AI Service)
- Compute similarity scores using SBERT model
- Store matches in database
- Send push notifications (Firebase FCM)
- Send email notifications (SMTP)
- Create in-app notifications

**Key Data Flows**:
- From Process 3.0: Item records
- To User: Recommended matches, Notifications
- To FastAPI AI Service: Item descriptions (reference + candidates)
- From FastAPI AI Service: Matched items with similarity scores
- To Firebase FCM: Push notification payloads
- To Email Server: Email notifications

**Data Stores**: `matches` table, `notifications` table, `device_tokens` table

**External Services**: FastAPI AI Service, Firebase Cloud Messaging, SMTP Email Server

---

### 5.0 Search & Filter
**Function**: Provides search and filtering capabilities for items
- Keyword search (title, description)
- Category filter
- Date range filter
- Type filter (lost/found)
- Full-text search
- Relevance sorting

**Key Data Flows**:
- From User: Search/filter requests
- To User: Filtered item list

**Data Stores**: `lost_items` table, `found_items` table, `categories` table

---

### 6.0 Messaging
**Function**: Handles communication between users and administrators
- Receive user messages/feedback
- Store messages
- Route messages to appropriate admin
- Notification to admin about new messages

**Key Data Flows**:
- From User: Messages for admin
- To Admin: User messages

**Data Store**: `notifications` table (with type: `admin_message`)

---

### 7.0 Campus Map & AR Navigation
**Function**: Provides campus navigation and AR guidance
- Fetch AR locations (POIs)
- Get Google Maps directions
- Geocoding (address to coordinates)
- AR navigation overlay (ARCore/ARKit)
- Route calculation
- Distance and ETA calculation

**Key Data Flows**:
- From User: AR navigation destination, 2D campus map with building markers
- To User: Navigation routes, AR overlays, Map data
- To Google Maps API: Location queries, route requests
- From Google Maps API: Directions, geocoding results, POI data

**Data Stores**: `ar_locations` table, `buildings` table

**External Service**: Google Maps API (Directions, Geocoding, Maps SDK)

---

### 8.0 Admin Dashboard & Reports
**Function**: Provides analytics, reporting, and system monitoring
- Dashboard data (statistics, charts)
- Analytics calculations
- Export reports (CSV, Excel)
- System status monitoring
- Queue status
- AI service health check

**Key Data Flows**:
- From Admin: Statistical summary requests
- To Admin: Statistical summaries

**Data Stores**: All tables (aggregated queries)

**Metrics**: Total items, active claims, match statistics, user activity, collection deadlines, SLA breaches

---

## Required Data Flows

### User → Processes
1. **Lost item details** → Process 2.0 (Lost Item Management)
2. **Lost item list** → Process 2.0 (Lost Item Management)
3. **Search/filter requests** → Process 5.0 (Search & Filter)
4. **Messages for admin** → Process 6.0 (Messaging)
5. **AR navigation destination** → Process 7.0 (Campus Map & AR Navigation)
6. **2D campus map with building markers** → Process 7.0 (Campus Map & AR Navigation)

### Processes → User
1. **Recommended matches** ← Process 4.0 (Item Matching & Notifications)
2. **Notifications** ← Process 4.0 (Item Matching & Notifications)

### Admin → Processes
1. **User account information** → Process 1.0 (User Management)
2. **User account list** → Process 1.0 (User Management)
3. **Found item detail** → Process 3.0 (Found Item Management)
4. **Found item records** → Process 3.0 (Found Item Management)
5. **Record updates/deletions** → Process 3.0 (Found Item Management)
6. **Statistical summary requests** → Process 8.0 (Admin Dashboard & Reports)

### Processes → Admin
1. **User messages** ← Process 6.0 (Messaging)
2. **Statistical summaries** ← Process 8.0 (Admin Dashboard & Reports)

### Inter-Process Flows
1. **Item data** → Process 2.0 → Process 3.0 (Lost item data for matching)
2. **Item records** → Process 3.0 → Process 4.0 (Found item records for AI matching)

---

## Diagram Structure Requirements

1. **External Entities** (Top/Bottom):
   - User (Mobile User) - Left side
   - Admin (Admin User) - Right side

2. **System Boundary** (Center):
   - Large rectangle containing all 8 processes
   - Processes should be arranged logically:
     - Top row: 1.0 User Management, 2.0 Lost Item Management
     - Middle rows: 3.0 Found Item Management, 4.0 Item Matching & Notifications, 5.0 Search & Filter
     - Bottom rows: 6.0 Messaging, 7.0 Campus Map & AR Navigation, 8.0 Admin Dashboard & Reports

3. **Data Flow Arrows**:
   - Label all arrows with descriptive data flow names
   - Show direction (→) clearly
   - Include both input and output flows

4. **Process Numbering**:
   - Use format: `1.0`, `2.0`, `3.0`, etc.
   - Include process name and key functions

5. **External Services** (Optional but Recommended):
   - FastAPI AI Service (connected to Process 4.0)
   - Firebase FCM (connected to Process 4.0)
   - Google Maps API (connected to Process 7.0)
   - Email Server (connected to Process 4.0)
   - MySQL Database (shown as external data store)

---

## Diagram Format

Use ASCII art with the following conventions:
- **External Entities**: Rounded rectangles `┌──────┐`
- **System Boundary**: Large rectangle `┌────────────────────┐`
- **Processes**: Rounded rectangles with process number and name
- **Data Flows**: Arrows `→` or `←` with labels
- **Data Stores**: Open rectangles (if shown) `┌──────┐` with `D1:`, `D2:`, etc.

---

## Key API Endpoints Reference

### User Management (Process 1.0)
- `POST /api/register` - User registration
- `POST /api/login` - User login
- `POST /api/auth/google` - Google OAuth
- `POST /api/auth/forgot-password` - Password reset request
- `POST /api/auth/reset-password` - Password reset
- `GET /api/user` - Get user profile
- `GET /api/me` - Get my profile

### Lost Item Management (Process 2.0)
- `POST /api/items` (type: "lost") - Create lost item
- `PUT /api/items/{id}` - Update lost item
- `DELETE /api/items/{id}` - Delete lost item
- `GET /api/items?type=lost` - List lost items

### Found Item Management (Process 3.0)
- `POST /api/items` (type: "found") - Create found item
- `PUT /api/items/{id}` - Update found item
- `DELETE /api/items/{id}` - Delete found item
- `GET /api/items?type=found` - List found items
- `POST /api/items/{id}/claim` - Submit claim

### Item Matching & Notifications (Process 4.0)
- `POST /api/items/{id}/compute-matches` - Trigger AI matching
- `GET /api/items/{id}/matches` - Get matches for item
- `GET /api/items/recommended` - Get personalized recommendations
- `GET /api/notifications` - List notifications
- `POST /api/device-tokens` - Register FCM token

### Search & Filter (Process 5.0)
- `GET /api/items?query=...` - Keyword search
- `GET /api/items?category=...` - Category filter
- `GET /api/items?dateFrom=...&dateTo=...` - Date range filter
- `GET /api/items?type=...` - Type filter

### Messaging (Process 6.0)
- `POST /api/notifications/test-send/{user}` - Send admin message
- Contact forms (web dashboard)

### Campus Map & AR Navigation (Process 7.0)
- `GET /api/ar/locations` - Get AR locations/POIs
- Google Maps Directions API calls (from Flutter app)
- Google Maps Geocoding API calls

### Admin Dashboard & Reports (Process 8.0)
- `GET /dashboard/data` - Dashboard analytics
- `GET /dashboard/chart-data` - Chart data
- `GET /dashboard/export` - Export reports
- `GET /admin/claims` - List claims
- `POST /admin/claims/{id}/approve` - Approve claim
- `POST /admin/claims/{id}/reject` - Reject claim

---

## Database Tables Reference

- **D1: users** - User accounts
- **D2: lost_items** - Lost item records
- **D3: found_items** - Found item records
- **D4: matches** - AI-generated matches
- **D5: claimed_items** - Claim requests
- **D6: notifications** - In-app notifications
- **D7: device_tokens** - FCM device tokens
- **D8: ar_locations** - POI coordinates
- **D9: categories** - Item categories
- **D10: buildings** - Building information
- **D11: activity_logs** - System activity tracking

---

## Instructions for Cursor AI

1. **Analyze the entire codebase**:
   - Review `routes/api.php` for API endpoints
   - Review `app/Http/Controllers/` for controller logic
   - Review `app/Models/` for data models
   - Review `database/migrations/` for database schema
   - Review `app/Services/` for service integrations
   - Review `app/Jobs/` for background processes

2. **Map processes to code**:
   - Identify which controllers/services correspond to each process
   - Identify data flows based on API routes and controller methods
   - Identify inter-process communication (queue jobs, service calls)

3. **Create the diagram**:
   - Use ASCII art format
   - Include all 8 processes with proper numbering
   - Show all data flows with descriptive labels
   - Arrange processes logically (top to bottom, left to right)
   - Show external entities (User, Admin) clearly
   - Optionally show external services (FastAPI, FCM, Google Maps, Email, Database)

4. **Document the diagram**:
   - Provide process descriptions
   - List all data flows in tables
   - Reference API endpoints
   - Reference database tables

5. **Verify completeness**:
   - Check that all user flows are covered (mobile user → processes)
   - Check that all admin flows are covered (admin → processes)
   - Check that inter-process flows are shown
   - Check that external service integrations are documented

---

## Expected Output Format

The output should be a markdown file (`LEVEL_1_DATA_FLOW_DIAGRAM.md`) containing:

1. **Title and Overview**
2. **Visual Diagram** (ASCII art)
3. **Process Descriptions** (for each of the 8 processes)
4. **Data Flow Summary Tables**:
   - User → Processes
   - Processes → User
   - Admin → Processes
   - Processes → Admin
   - Inter-Process Flows
5. **Data Stores Reference**
6. **External Entities Description**
7. **Key Characteristics**

---

## Reference Files

- `SYSTEM_ARCHITECTURE_OVERVIEW.md` - System architecture details
- `CONTEXT_FLOW_DIAGRAM.md` - Level 0 DFD (system as single process)
- `routes/api.php` - API route definitions
- `app/Http/Controllers/` - Controller implementations
- `database/migrations/` - Database schema

---

## Notes

- The diagram should show the system from both **mobile user** and **admin user** perspectives
- All data flows should be labeled with clear, descriptive names
- Processes should be numbered sequentially (1.0 through 8.0)
- The diagram should be readable and well-organized
- External services can be shown outside the system boundary or as part of process descriptions

---

**Generate the Level 1 Data Flow Diagram following these specifications.**

---

# Level 1 Data Flow Diagram
## NavistFind AI-Powered Lost & Found and AR Navigation System

## Visual Diagram

```
                    ┌──────────────┐                          ┌──────────────┐
                    │    User      │                          │    Admin     │
                    │ (Mobile App) │                          │ (Web Dash)   │
                    └──────┬───────┘                          └──────┬───────┘
                           │                                        │
                           │                                        │
        ┌──────────────────┴──────────────────────────────────────┴──────────────────┐
        │                                                                                │
        │                    NAVISTFIND SYSTEM (Level 1 DFD)                            │
        │                                                                                │
        │  ┌────────────────────┐              ┌────────────────────┐                  │
        │  │ 1.0 User           │              │ 2.0 Lost Item      │                  │
        │  │ Management         │              │ Management         │                  │
        │  │                    │              │                    │                  │
        │  │ • Registration     │              │ • Create/Update/   │                  │
        │  │ • Login (Email/    │              │   Delete Lost     │                  │
        │  │   Google OAuth)    │              │   Items           │                  │
        │  │ • Password Reset   │              │ • List Lost Items  │                  │
        │  │ • Profile Mgmt     │              │ • Status Mgmt      │                  │
        │  │ • Role Assignment  │              │                    │                  │
        │  └──────────┬─────────┘              └──────────┬─────────┘                  │
        │             │                                   │                            │
        │             │                                   │                            │
        │  ┌──────────▼─────────┐              ┌─────────▼──────────┐                │
        │  │ 3.0 Found Item     │              │ 4.0 Item Matching  │                │
        │  │ Management         │              │ & Notifications     │                │
        │  │                    │              │                     │                │
        │  │ • Create/Update/  │              │ • AI Matching       │                │
        │  │   Delete Found    │              │   (FastAPI)         │                │
        │  │   Items           │              │ • Push Notifications│                │
        │  │ • List Found Items│              │   (FCM)             │                │
        │  │ • Claim Processing│              │ • Email Notifications│                │
        │  │ • Collection      │              │ • In-app Notifications│                │
        │  │   Deadline Mgmt   │              │                     │                │
        │  └──────────┬─────────┘              └──────────┬──────────┘                │
        │             │                                   │                            │
        │             │                                   │                            │
        │  ┌──────────▼─────────┐              ┌─────────▼──────────┐                │
        │  │ 5.0 Search & Filter │              │ 6.0 Messaging       │                │
        │  │                    │              │                     │                │
        │  │ • Keyword Search   │              │ • User Messages     │                │
        │  │ • Category Filter  │              │ • Admin Routing     │                │
        │  │ • Date Range Filter│              │ • Notification      │                │
        │  │ • Type Filter      │              │                     │                │
        │  │ • Relevance Sort   │              │                     │                │
        │  └──────────┬─────────┘              └──────────┬──────────┘                │
        │             │                                   │                            │
        │             │                                   │                            │
        │  ┌──────────▼─────────┐              ┌─────────▼──────────┐                │
        │  │ 7.0 Campus Map &   │              │ 8.0 Admin Dashboard │                │
        │  │ AR Navigation      │              │ & Reports           │                │
        │  │                    │              │                     │                │
        │  │ • AR Locations     │              │ • Dashboard Data    │                │
        │  │ • Google Maps      │              │ • Analytics         │                │
        │  │   Directions       │              │ • Export Reports    │                │
        │  │ • Geocoding        │              │ • System Monitoring │                │
        │  │ • AR Overlay       │              │ • Queue Status      │                │
        │  └──────────┬─────────┘              └──────────┬──────────┘                │
        │             │                                   │                            │
        └─────────────┼───────────────────────────────────┼────────────────────────────┘
                      │                                   │
                      │                                   │
        ┌─────────────▼───────────┐         ┌─────────────▼───────────┐
        │  FastAPI AI Service     │         │  Firebase FCM          │
        │  (SBERT Matching)       │         │  (Push Notifications)  │
        └─────────────────────────┘         └─────────────────────────┘
                      │                                   │
        ┌─────────────▼───────────┐         ┌─────────────▼───────────┐
        │  Google Maps API        │         │  Email Server (SMTP)    │
        │  (Directions/Geocoding) │         │  (Email Notifications) │
        └─────────────────────────┘         └─────────────────────────┘
                      │
        ┌─────────────▼───────────┐
        │  MySQL Database         │
        │  (Data Storage)         │
        └─────────────────────────┘
```

## Process Descriptions

### 1.0 User Management
**Function**: Handles user account operations and authentication.

**Key Operations**:
- User registration (`POST /api/register`)
- User login - Email/Password (`POST /api/login`)
- Google OAuth sign-in (`POST /api/auth/google`)
- Password reset (`POST /api/auth/forgot-password`, `POST /api/auth/reset-password`)
- Profile management (`GET /api/user`, `GET /api/me`)
- Role assignment (student, staff, admin)

**Data Stores**: `users` table

**Controllers**: `App\Http\Controllers\Api\AuthController`, `App\Http\Controllers\Api\ProfileController`

---

### 2.0 Lost Item Management
**Function**: Manages lost item lifecycle from creation to resolution.

**Key Operations**:
- Create lost items (`POST /api/items` with `type: "lost"`)
- Update lost items (`PUT /api/items/{id}`)
- Delete lost items (`DELETE /api/items/{id}`)
- List lost items (`GET /api/items?type=lost`)
- Status management: `LOST_REPORTED` → `LOST_MATCHED` → `RESOLVED`

**Data Stores**: `lost_items` table

**Controllers**: `App\Http\Controllers\Api\ItemController`

---

### 3.0 Found Item Management
**Function**: Manages found item lifecycle including claim processing.

**Key Operations**:
- Create found items (`POST /api/items` with `type: "found"`)
- Update found items (`PUT /api/items/{id}`)
- Delete found items (`DELETE /api/items/{id}`)
- List found items (`GET /api/items?type=found`)
- Claim submission (`POST /api/items/{id}/claim`)
- Status management: `FOUND_UNCLAIMED` → `CLAIM_PENDING` → `CLAIM_APPROVED` → `COLLECTED`
- Collection deadline management
- Collection reminder scheduling

**Data Stores**: `found_items` table, `claimed_items` table

**Controllers**: `App\Http\Controllers\Api\ItemController`, `App\Http\Controllers\Admin\ClaimsController`

---

### 4.0 Item Matching & Notifications
**Function**: AI-powered matching and notification delivery.

**Key Operations**:
- Trigger AI matching (`POST /api/items/{id}/compute-matches`)
- Compute similarity scores using SBERT model (via FastAPI)
- Store matches in database
- Send push notifications via Firebase FCM
- Send email notifications via SMTP
- Create in-app notifications
- Get matches (`GET /api/items/{id}/matches`)
- Get recommendations (`GET /api/items/recommended`)

**Data Stores**: `matches` table, `notifications` table, `device_tokens` table

**External Services**: FastAPI AI Service, Firebase Cloud Messaging, SMTP Email Server

**Controllers**: `App\Http\Controllers\Api\ItemController`, `App\Http\Controllers\Api\RecommendationController`
**Services**: `App\Services\AIService`, `App\Services\FcmService`
**Jobs**: `App\Jobs\ComputeItemMatches`, `App\Jobs\SendNotificationJob`

---

### 5.0 Search & Filter
**Function**: Provides search and filtering capabilities for items.

**Key Operations**:
- Keyword search (title, description) - `GET /api/items?query=...`
- Category filter - `GET /api/items?category=...`
- Date range filter - `GET /api/items?dateFrom=...&dateTo=...`
- Type filter (lost/found) - `GET /api/items?type=...`
- Full-text search
- Relevance sorting - `GET /api/items?sort=relevance`

**Data Stores**: `lost_items` table, `found_items` table, `categories` table

**Controllers**: `App\Http\Controllers\Api\ItemController`

---

### 6.0 Messaging
**Function**: Handles communication between users and administrators.

**Key Operations**:
- Receive user messages/feedback
- Store messages in notifications table
- Route messages to appropriate admin
- Notification to admin about new messages

**Data Stores**: `notifications` table (with type: `admin_message`)

**Implementation**: 
- `POST /api/notifications/test-send/{user}` (admin test send)
- Contact forms (web dashboard)

**Controllers**: `App\Http\Controllers\Api\NotificationController`

---

### 7.0 Campus Map & AR Navigation
**Function**: Provides campus navigation and AR guidance.

**Key Operations**:
- Fetch AR locations/POIs (from `ar_locations` table)
- Get Google Maps directions (Directions API)
- Geocoding (address to coordinates)
- AR navigation overlay (ARCore/ARKit)
- Route calculation
- Distance and ETA calculation

**Data Stores**: `ar_locations` table, `buildings` table

**External Services**: Google Maps API (Directions, Geocoding, Maps SDK)

**Controllers**: `App\Http\Controllers\CampusMapController`

**Note**: AR navigation is primarily handled in Flutter app with Google Maps API integration.

---

### 8.0 Admin Dashboard & Reports
**Function**: Provides analytics, reporting, and system monitoring.

**Key Operations**:
- Dashboard data (`GET /dashboard/data`)
- Chart data (`GET /dashboard/chart-data`)
- Export reports (`GET /dashboard/export`)
- Analytics calculations
- System status monitoring
- Queue status
- AI service health check (`GET /api/ai/health`)

**Data Stores**: All tables (aggregated queries)

**Metrics**: Total items, active claims, match statistics, user activity, collection deadlines, SLA breaches

**Controllers**: `App\Http\Controllers\DashboardController`, `App\Http\Controllers\Api\AIController`

---

## Data Flow Summary Tables

### User → Processes

| Data Flow | To Process | Description | API Endpoint |
|-----------|------------|-------------|--------------|
| Lost item details | 2.0 Lost Item Management | Create/update lost item | `POST /api/items`, `PUT /api/items/{id}` |
| Lost item list | 2.0 Lost Item Management | Request list of lost items | `GET /api/items?type=lost` |
| Search/filter requests | 5.0 Search & Filter | Keyword, category, date filters | `GET /api/items?query=...&category=...` |
| Messages for admin | 6.0 Messaging | User feedback/messages | `POST /api/notifications/test-send/{user}` |
| AR navigation destination | 7.0 Campus Map & AR Navigation | Selected POI/destination | Google Maps Directions API |
| 2D campus map request | 7.0 Campus Map & AR Navigation | Request map with building markers | `GET /campus-map` (web) |
| Authentication credentials | 1.0 User Management | Login/register data | `POST /api/login`, `POST /api/register` |
| Device token | 4.0 Item Matching & Notifications | FCM token registration | `POST /api/device-tokens` |
| Claim submission | 3.0 Found Item Management | Submit claim for found item | `POST /api/items/{id}/claim` |

### Processes → User

| Data Flow | From Process | Description | API Endpoint |
|-----------|--------------|-------------|--------------|
| Recommended matches | 4.0 Item Matching & Notifications | AI-matched items with scores | `GET /api/items/{id}/matches`, `GET /api/items/recommended` |
| Notifications | 4.0 Item Matching & Notifications | Push notifications (FCM) + in-app | `GET /api/notifications` |
| Lost item list | 2.0 Lost Item Management | Paginated list of lost items | `GET /api/items?type=lost` |
| Found item list | 3.0 Found Item Management | Paginated list of found items | `GET /api/items?type=found` |
| Filtered item list | 5.0 Search & Filter | Search/filter results | `GET /api/items?query=...` |
| Navigation routes | 7.0 Campus Map & AR Navigation | Directions data from Google Maps | Google Maps Directions API response |
| AR overlays | 7.0 Campus Map & AR Navigation | AR navigation overlay data | ARCore/ARKit rendering |
| Map data | 7.0 Campus Map & AR Navigation | Campus map with POIs | `GET /campus-map` (web) |
| Authentication token | 1.0 User Management | Sanctum bearer token | `POST /api/login` response |
| User profile | 1.0 User Management | User information | `GET /api/user`, `GET /api/me` |

### Admin → Processes

| Data Flow | To Process | Description | API/Route |
|-----------|------------|-------------|-----------|
| User account information | 1.0 User Management | Create/update/delete users | `POST /users`, `PUT /users/{id}`, `DELETE /users/{id}` |
| User account list | 1.0 User Management | Request user list | `GET /users` |
| Found item detail | 3.0 Found Item Management | Create/update found items | `POST /items`, `PUT /items/{id}` |
| Found item records | 3.0 Found Item Management | Request found item list | `GET /item` |
| Record updates/deletions | 3.0 Found Item Management | Bulk update/delete items | `POST /items/bulk-update`, `POST /items/bulk-delete` |
| Claim management actions | 3.0 Found Item Management | Approve/reject claims | `POST /admin/claims/{id}/approve`, `POST /admin/claims/{id}/reject` |
| Statistical summary requests | 8.0 Admin Dashboard & Reports | Dashboard/analytics requests | `GET /dashboard/data`, `GET /dashboard/chart-data` |

### Processes → Admin

| Data Flow | From Process | Description | API/Route |
|-----------|--------------|-------------|-----------|
| User messages | 6.0 Messaging | Messages from users | Admin dashboard notifications |
| Statistical summaries | 8.0 Admin Dashboard & Reports | Dashboard analytics data | `GET /dashboard/data` response |
| User account list | 1.0 User Management | List of all users | `GET /users` response |
| Found item records | 3.0 Found Item Management | List of found items | `GET /item` response |
| Claim queue | 3.0 Found Item Management | Pending claims list | `GET /admin/claims` |
| Match queue | 4.0 Item Matching & Notifications | AI match queue | `GET /admin/matches` |
| System status | 8.0 Admin Dashboard & Reports | System health/queue status | Dashboard monitoring |

### Inter-Process Flows

| Data Flow | From Process | To Process | Description |
|-----------|--------------|------------|-------------|
| Item data | 2.0 Lost Item Management | 3.0 Found Item Management | Lost item data for matching (via database) |
| Item records | 3.0 Found Item Management | 4.0 Item Matching & Notifications | Found item records for AI matching |
| Match results | 4.0 Item Matching & Notifications | 2.0 Lost Item Management | Match results update lost item status |
| Claim data | 3.0 Found Item Management | 4.0 Item Matching & Notifications | Claim approval triggers notifications |

### System ↔ External Services

| Data Flow | Direction | Service | Description |
|-----------|-----------|---------|-------------|
| Item descriptions | System → FastAPI | FastAPI AI Service | Reference + candidate items for matching |
| Matched items | FastAPI → System | FastAPI AI Service | Matched items with similarity scores |
| Push notification payloads | System → FCM | Firebase FCM | FCM notification payloads |
| Device tokens | System ↔ FCM | Firebase FCM | Token registration/unregistration |
| Location queries | System → Google Maps | Google Maps API | Directions/geocoding requests |
| Directions/geocoding | Google Maps → System | Google Maps API | Route data, coordinates |
| Email notifications | System → Email Server | SMTP | Claim approvals, reminders, match notifications |
| Data storage/retrieval | System ↔ Database | MySQL | All CRUD operations |

---

## Data Stores Reference

| Store ID | Store Name | Description | Key Tables |
|----------|------------|-------------|------------|
| D1 | users | User accounts and authentication | `users` |
| D2 | lost_items | Lost item records | `lost_items` |
| D3 | found_items | Found item records | `found_items` |
| D4 | matches | AI-generated matches | `matches` |
| D5 | claimed_items | Claim requests | `claimed_items` |
| D6 | notifications | In-app notifications | `notifications` |
| D7 | device_tokens | FCM device tokens | `device_tokens` |
| D8 | ar_locations | AR navigation POIs | `ar_locations` |
| D9 | categories | Item categories | `categories` |
| D10 | buildings | Building information | `buildings` |
| D11 | activity_logs | System activity tracking | `activity_logs` |

---

## External Entities

### 1. User (Mobile User - Student/Faculty)
- **Interface**: Flutter mobile application
- **Primary Functions**:
  - Create lost/found items
  - Search and filter items
  - Submit claims for found items
  - Use AR navigation
  - Receive notifications
  - View matches and recommendations

### 2. Admin (Admin User - Staff/Personnel)
- **Interface**: Laravel Blade web dashboard
- **Primary Functions**:
  - Manage user accounts
  - Manage found items
  - Approve/reject claims
  - View analytics and reports
  - Monitor system status
  - Manage categories and AR locations

### 3. External Services
- **FastAPI AI Service**: SBERT-based semantic similarity matching
- **Firebase Cloud Messaging**: Push notification delivery
- **Google Maps API**: Directions, geocoding, and maps
- **Email Server (SMTP)**: Email notification delivery
- **MySQL Database**: Data persistence (shown as external for clarity)

---

## Key Characteristics

### System Boundary
- **8 Major Processes**: User Management, Lost Item Management, Found Item Management, Item Matching & Notifications, Search & Filter, Messaging, Campus Map & AR Navigation, Admin Dashboard & Reports
- **External Entities**: User (Mobile), Admin (Web)
- **External Services**: FastAPI AI, Firebase FCM, Google Maps API, Email Server, MySQL Database

### Data Flow Types
- **Bidirectional**: Most processes have both input and output flows
- **Unidirectional**: Some flows are one-way (e.g., System → Email Server)
- **Real-time**: Push notifications, AR navigation updates
- **Batch**: Queue jobs (AI matching, notifications)

### Process Interactions
- **Sequential**: Lost item creation → AI matching → Notifications
- **Parallel**: Search & Filter operates independently
- **Event-driven**: Claim approval triggers notifications
- **Scheduled**: Collection reminders, SLA monitoring

### Technology Stack
- **Backend**: Laravel 12 REST API with Sanctum authentication
- **Frontend**: Flutter mobile app + Laravel Blade web dashboard
- **AI Service**: FastAPI with SBERT for semantic similarity
- **Database**: MySQL on Hostinger
- **External**: Firebase FCM, Google Maps API, SMTP mail

---

**Document Version**: 1.0  
**Last Updated**: 2025-01-XX  
**Related Documents**: `SYSTEM_ARCHITECTURE_OVERVIEW.md`, `CONTEXT_FLOW_DIAGRAM.md`

