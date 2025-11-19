# Level 1 Data Flow Diagram (DFD)
## NavistFind AI-Powered Lost & Found and AR Navigation System

---

## Overview

The Level 1 DFD decomposes the NavistFind system into major functional processes, showing how data flows between external entities (User, Admin) and internal processes, as well as between processes themselves.

---

## Diagram

```
┌──────────────┐                                                      ┌──────────────┐
│    User      │                                                      │    Admin     │
│ (Student/    │                                                      │ (Staff/      │
│  Faculty)    │                                                      │  Personnel)  │
└──────┬───────┘                                                      └──────┬───────┘
       │                                                                      │
       │ Lost item details                                                  │ User account information
       │ Lost item list                                                     │ User account list
       │                                                                    │
       │                                                                    │
       ▼                                                                    ▼
┌─────────────────────────────────────────────────────────────────────────────────────┐
│                                                                                       │
│                               SYSTEM PROCESSES                                       │
│                                                                                       │
│  ┌──────────────────────────┐         ┌──────────────────────────┐                 │
│  │  1.0 User Management     │         │  2.0 Lost Item          │                 │
│  │                          │         │     Management          │                 │
│  │  • User registration     │         │                          │                 │
│  │  • Authentication        │         │  • Create lost items     │                 │
│  │  • Profile management    │         │  • Update lost items     │                 │
│  │  • Role assignment       │         │  • Delete lost items     │                 │
│  │                          │         │  • List lost items       │                 │
│  └──────────────────────────┘         │  • Status management    │                 │
│                                        └───────────┬──────────────┘                 │
│                                                    │                                │
│                                                    │ Item data                      │
│                                                    │                                │
│                                                    ▼                                │
│                                        ┌──────────────────────────┐                 │
│                                        │  3.0 Found Item         │                 │
│                                        │     Management          │                 │
│                                        │                          │                 │
│                                        │  • Create found items   │                 │
│                                        │  • Update found items   │                 │
│                                        │  • Delete found items   │                 │
│                                        │  • List found items     │                 │
│                                        │  • Status management    │                 │
│                                        │                          │                 │
│                                        └───────────┬──────────────┘                 │
│                                                    │                                │
│                                                    │ Item records                   │
│                                                    │                                │
│                                                    ▼                                │
│                                        ┌──────────────────────────┐                 │
│                                        │  4.0 Item Matching &     │                 │
│                                        │     Notifications        │                 │
│                                        │                          │                 │
│                                        │  • AI-powered matching  │                 │
│                                        │  • Similarity scoring    │                 │
│                                        │  • Match notifications  │                 │
│                                        │  • Push notifications    │                 │
│                                        │  • Email notifications  │                 │
│                                        │                          │                 │
│                                        └───────────┬──────────────┘                 │
│                                                    │                                │
│                                                    │                                │
│                                                    │ Recommended matches            │
│                                                    │ Notifications                  │
│                                                    │                                │
│                                                    ▼                                │
│                                        ┌──────────────────────────┐                 │
│                                        │  5.0 Search & Filter    │                 │
│                                        │                          │                 │
│                                        │  • Keyword search       │                 │
│                                        │  • Category filter      │                 │
│                                        │  • Date range filter    │                 │
│                                        │  • Type filter          │                 │
│                                        │  • Full-text search     │                 │
│                                        │                          │                 │
│                                        └──────────────────────────┘                 │
│                                                                                       │
│  ┌──────────────────────────┐         ┌──────────────────────────┐                 │
│  │  6.0 Messaging          │         │  7.0 Campus Map & AR     │                 │
│  │                          │         │     Navigation           │                 │
│  │  • User messages        │         │                          │                 │
│  │  • Admin communication  │         │  • AR location data      │                 │
│  │  • Contact forms        │         │  • Google Maps integration│                 │
│  │                          │         │  • Directions API        │                 │
│  │                          │         │  • ARCore/ARKit overlay │                 │
│  │                          │         │  • POI management       │                 │
│  └───────────┬──────────────┘         │                          │                 │
│              │                        └──────────────────────────┘                 │
│              │                                                                       │
│              │ User messages                                                        │
│              │                                                                       │
│              ▼                                                                       │
│  ┌──────────────────────────┐                                                     │
│  │  8.0 Admin Dashboard &    │                                                     │
│  │     Reports               │                                                     │
│  │                           │                                                     │
│  │  • Analytics dashboard   │                                                     │
│  │  • Statistical summaries  │                                                     │
│  │  • Chart data             │                                                     │
│  │  • Export reports         │                                                     │
│  │  • System monitoring      │                                                     │
│  │                           │                                                     │
│  └──────────────────────────┘                                                     │
│                                                                                       │
└─────────────────────────────────────────────────────────────────────────────────────┘
       │                                                                      │
       │ Search/filter requests                                               │ Statistical summary requests
       │ Messages for admin                                                   │
       │ AR navigation destination                                            │
       │ 2D campus map with building markers                                  │
       │                                                                      │
       │                                                                      │ Statistical summaries
       │                                                                      │
       └──────────────────────────────────────────────────────────────────────┘
```

---

## Process Descriptions

### 1.0 User Management

**Function**: Handles user account operations and authentication.

**Input Flows**:
- From Admin: User account information, User account list

**Output Flows**:
- To Admin: User account list
- To System: Authenticated user sessions, User profiles

**Key Operations**:
- User registration (`POST /api/register`)
- User login (`POST /api/login`)
- Google OAuth authentication (`POST /api/auth/google`)
- Password reset (`POST /api/auth/forgot-password`, `POST /api/auth/reset-password`)
- Profile management (`GET /api/user`, `GET /api/me`)
- Role assignment (admin, staff, student)

**Data Stores**:
- `users` table

---

### 2.0 Lost Item Management

**Function**: Manages lost item lifecycle from creation to resolution.

**Input Flows**:
- From User: Lost item details, Lost item list

**Output Flows**:
- To User: Lost item list, Lost item details
- To Process 3.0: Item data (for matching)

**Key Operations**:
- Create lost item (`POST /api/items` with `type: "lost"`)
- Update lost item (`PUT /api/items/{id}`)
- Delete lost item (`DELETE /api/items/{id}`)
- List lost items (`GET /api/items?type=lost`)
- Status management (open → matched → closed/resolved)

**Data Stores**:
- `lost_items` table

**Status Flow**:
- `LOST_REPORTED` → `LOST_MATCHED` → `RESOLVED`

---

### 3.0 Found Item Management

**Function**: Manages found item lifecycle including claim processing.

**Input Flows**:
- From Admin: Found item detail, Found item records, Record updates/deletions
- From Process 2.0: Item data (for matching)

**Output Flows**:
- To Admin: Found item records
- To Process 4.0: Item records (for AI matching)

**Key Operations**:
- Create found item (`POST /api/items` with `type: "found"`)
- Update found item (`PUT /api/items/{id}`)
- Delete found item (`DELETE /api/items/{id}`)
- List found items (`GET /api/items?type=found`)
- Status management (unclaimed → claim_pending → claim_approved → collected)
- Collection deadline management
- Collection reminder scheduling

**Data Stores**:
- `found_items` table
- `claimed_items` table

**Status Flow**:
- `FOUND_UNCLAIMED` → `CLAIM_PENDING` → `CLAIM_APPROVED` → `COLLECTED`

---

### 4.0 Item Matching & Notifications

**Function**: AI-powered matching and notification delivery.

**Input Flows**:
- From Process 3.0: Item records

**Output Flows**:
- To User: Recommended matches, Notifications

**Key Operations**:
- Trigger AI matching (`POST /api/items/{id}/compute-matches`)
- Get matches for item (`GET /api/items/{id}/matches`)
- Get personalized recommendations (`GET /api/items/recommended`)
- Compute similarity scores (via FastAPI AI Service)
- Store matches in database
- Send push notifications (FCM)
- Send email notifications (SMTP)
- Create in-app notifications

**External Services**:
- FastAPI AI Service (SBERT model)
- Firebase Cloud Messaging (FCM)
- SMTP Email Server

**Data Stores**:
- `matches` table
- `notifications` table
- `device_tokens` table

**Workflow**:
1. Receive item records from Found Item Management
2. Call FastAPI AI Service with item descriptions
3. Receive matched items with similarity scores
4. Store matches in `matches` table
5. Dispatch notification jobs
6. Send push notifications via FCM
7. Send email notifications via SMTP

---

### 5.0 Search & Filter

**Function**: Provides search and filtering capabilities for items.

**Input Flows**:
- From User: Search/filter requests

**Output Flows**:
- To User: Filtered item list

**Key Operations**:
- Keyword search (`GET /api/items?query=...`)
- Category filter (`GET /api/items?category=...`)
- Date range filter (`GET /api/items?dateFrom=...&dateTo=...`)
- Type filter (`GET /api/items?type=lost|found`)
- Combined filters
- Full-text search (MySQL full-text indexes)
- Relevance sorting

**Data Stores**:
- `lost_items` table (full-text: title, description)
- `found_items` table (full-text: title, description)
- `categories` table

**Search Capabilities**:
- Title search
- Description search
- Location search
- Category-based filtering
- Date-based filtering
- Status filtering

---

### 6.0 Messaging

**Function**: Handles communication between users and administrators.

**Input Flows**:
- From User: Messages for admin

**Output Flows**:
- To Admin: User messages

**Key Operations**:
- Receive user messages/feedback
- Store messages
- Route messages to appropriate admin
- Notification to admin about new messages
- Message status tracking (read/unread)

**Data Stores**:
- `notifications` table (with type: `admin_message`)
- `activity_logs` table (optional)

**Implementation**:
- Via notification system (`POST /api/notifications/test-send/{user}`)
- Contact forms (web dashboard)
- In-app messaging (future enhancement)

---

### 7.0 Campus Map & AR Navigation

**Function**: Provides campus navigation and AR guidance.

**Input Flows**:
- From User: AR navigation destination, 2D campus map with building markers

**Output Flows**:
- To User: Navigation routes, AR overlays, Map data

**Key Operations**:
- Fetch AR locations (`GET /api/ar/locations`)
- Get Google Maps directions (Directions API)
- Geocoding (address to coordinates)
- AR navigation overlay (ARCore/ARKit)
- POI (Point of Interest) management
- Route calculation
- Distance and ETA calculation

**External Services**:
- Google Maps API (Directions, Geocoding, Maps SDK)
- ARCore (Android) / ARKit (iOS)

**Data Stores**:
- `ar_locations` table
- `buildings` table

**Features**:
- 2D campus map with building markers
- AR navigation with directional arrows
- Step-by-step directions
- Distance and ETA display
- Fallback to Google Maps app

---

### 8.0 Admin Dashboard & Reports

**Function**: Provides analytics, reporting, and system monitoring for administrators.

**Input Flows**:
- From Admin: Statistical summary requests

**Output Flows**:
- To Admin: Statistical summaries

**Key Operations**:
- Dashboard data (`GET /dashboard/data`)
- Chart data (`GET /dashboard/chart-data`)
- Export reports (`GET /dashboard/export`)
- Analytics calculations
- System status monitoring
- Queue status
- AI service health check

**Data Stores**:
- `lost_items` table (aggregated)
- `found_items` table (aggregated)
- `claimed_items` table (aggregated)
- `users` table (aggregated)
- `matches` table (aggregated)
- `notifications` table (aggregated)

**Metrics Provided**:
- Total items (lost/found)
- Active claims
- Match statistics
- User activity
- Collection deadlines
- SLA breaches
- System health

**Report Formats**:
- JSON (API)
- CSV export
- Excel export
- Real-time charts

---

## Data Flow Summary

### User → Processes

| Flow | To Process | Description |
|------|------------|-------------|
| Lost item details | 2.0 | Create/update lost items |
| Lost item list | 2.0 | View lost items |
| Search/filter requests | 5.0 | Search and filter items |
| Messages for admin | 6.0 | Send messages to admin |
| AR navigation destination | 7.0 | Request navigation to POI |
| 2D campus map with building markers | 7.0 | Request map data |

### Processes → User

| Flow | From Process | Description |
|------|--------------|-------------|
| Recommended matches | 4.0 | AI-matched items with scores |
| Notifications | 4.0 | Push and in-app notifications |

### Admin → Processes

| Flow | To Process | Description |
|------|------------|-------------|
| User account information | 1.0 | Create/update user accounts |
| User account list | 1.0 | View user accounts |
| Found item detail | 3.0 | Create/update found items |
| Found item records | 3.0 | View found items |
| Record updates/deletions | 3.0 | Update or delete items |
| Statistical summary requests | 8.0 | Request analytics data |

### Processes → Admin

| Flow | From Process | Description |
|------|--------------|-------------|
| User messages | 6.0 | Messages from users |
| Statistical summaries | 8.0 | Analytics and reports |

### Inter-Process Flows

| Flow | From → To | Description |
|------|-----------|-------------|
| Item data | 2.0 → 3.0 | Lost item data for matching |
| Item records | 3.0 → 4.0 | Found item records for AI matching |

---

## Data Stores (Not Shown in Diagram)

The following data stores are used by the processes:

- **D1: Users** - User accounts, authentication
- **D2: Lost Items** - Lost item records
- **D3: Found Items** - Found item records
- **D4: Matches** - AI-generated matches
- **D5: Claims** - Claim requests and approvals
- **D6: Notifications** - In-app notifications
- **D7: Device Tokens** - FCM device tokens
- **D8: AR Locations** - POI coordinates
- **D9: Categories** - Item categories
- **D10: Buildings** - Building information
- **D11: Activity Logs** - System activity tracking

---

## External Entities

### User (Student/Faculty)
- Primary users of the mobile application
- Create lost/found items
- Search and filter items
- Claim found items
- Use AR navigation
- Receive notifications

### Admin (Staff/Personnel)
- Administrative users of web dashboard
- Manage user accounts
- Manage found items
- Approve/reject claims
- View analytics and reports
- Monitor system status

---

## Key Characteristics

1. **Process Decomposition**: System broken down into 8 major functional processes
2. **Data Flow**: Clear data flows between entities and processes
3. **Inter-Process Communication**: Processes communicate via data flows
4. **External Services**: Process 4.0 interacts with FastAPI, FCM, and Email (not shown in diagram but documented)
5. **Data Stores**: Implicit data stores (database tables) used by all processes

---

## Related Diagrams

- **Context Flow Diagram (Level 0)**: Shows system as single process
- **Level 2 DFD**: Further decomposition of individual processes (optional)
- **System Architecture Diagram**: Technical component structure
- **Database ERD**: Entity relationships

---

**Document Version**: 1.0  
**Last Updated**: 2025-01-XX  
**Related Documents**: `CONTEXT_FLOW_DIAGRAM.md`, `SYSTEM_ARCHITECTURE_OVERVIEW.md`








