# AR Navigation Implementation Playbook

This guide distills everything in NavistFind into concrete steps for delivering camera-based Google-powered AR navigation on Android. Follow the sections in order; each builds on existing services (Flutter app, Laravel API, FastAPI SBERT).

---

## 1. Clarify Scope & Prerequisites
- **Target Platform**: Android phones/tablets with ARCore certification (Android 8+). Note in docs that iOS support is future work.
- **Navigation Mode**: Outdoor campus guidance from student location to official collection points (admin office, guard post, etc.).
- **APIs to Enable**:
  1. Google Maps SDK for Android
  2. Google Directions API
  3. (Optional) Distance Matrix API
  4. Google Places + Geocoding APIs
  5. ARCore Geospatial API
- **Keys & Billing**: Create/enable APIs in Google Cloud Console, restrict keys to package name/SHA1, and set up billing/quota alerts.
- **Test Device**: Pick one ARCore-compatible Android device to validate end-to-end flow today.

---

## 2. Backend Preparation (Laravel)
1. **Validate Data Models**  
   - `ArLocation` / `Building` tables must have `name`, `description`, `latitude`, `longitude`, optional `heading`, `category`.
   - Seed at least the key POIs (Admin Office, Lost & Found Window, Library, Gate, etc.).
2. **Expose APIs**  
   - `GET /api/ar/locations`: returns POI list with metadata.  
   - (Optional) `GET /api/ar/routes/{id}`: Laravel calls Google Directions once per destination (origin = POI, destination = admin office) and caches polyline + steps to reduce client-side quota.
3. **Secure Config**  
   - Store Google API key(s) in `.env` (`GOOGLE_MAPS_KEY`, `GOOGLE_DIRECTIONS_KEY`).  
   - Use Laravel config (`config/services.php`) to centralize usage.
4. **Admin Tools**  
   - In the web dashboard, ensure admins can edit POI coordinates. Use the existing map widget to verify markers align with campus map.

---

## 3. Flutter App Setup
1. **Dependencies**  
   - `ar_flutter_plugin` (or `arcore_flutter_plugin`) for ARCore integration.  
   - `google_maps_flutter` for 2D previews/fallback.  
   - `http` / `dio` for API calls.  
   - `geolocator` or `location` for GPS updates.
2. **Permissions**  
   - Request camera, fine location, coarse location, and motion sensor permissions.  
   - Update `AndroidManifest.xml` with Google Maps key and AR-required permissions.
3. **Environment Config**  
   - Use `flutter_dotenv` or equivalent to inject API keys if needed.  
   - Ensure release keystore SHA1 is registered with Google Cloud key restrictions.
4. **UI Entry Point**  
   - Add an “AR Navigation” button accessible from item detail or navigation tab.  
   - Provide POI picker (list or map) so user selects the destination.

---

## 4. Data Fetch & Routing
1. **Fetch POIs**  
   - Call `/api/ar/locations` when the AR screen loads. Cache results for offline fallback.  
   - Show info (name, building, description) to help user pick destination.
2. **Get Route**  
   - Option A: Device-side call `https://maps.googleapis.com/maps/api/directions/json` with `origin=<current lat,lng>`, `destination=<poi lat,lng>`, `mode=walking`.  
   - Option B: Call Laravel’s cached directions endpoint.  
   - Parse response for `polyline`, `legs[].steps[]` instructions, distance, ETA.
3. **Fallback Deep Link**  
   - Always display a button that opens Google Maps app via `https://www.google.com/maps/dir/?api=1&origin=...&destination=...&travelmode=walking`.  
   - This guarantees navigation even if AR fails.

---

## 5. AR Session & Pose Handling
1. **Start ARCore Session**  
   - Initialize `ARSessionManager` (via plugin) in Geospatial mode.  
   - Check `isSupported` and prompt user if device/lighting is insufficient.  
   - Obtain `GeospatialPose` (lat, lng, horizontalAccuracy, heading).
2. **Sensor Fusion**  
   - Subscribe to pose stream (or fused location updates) to refresh guidance.  
   - Filter noise with simple smoothing (average over last few readings).  
   - If accuracy > 10 m, show warning and suggest switching to map fallback.

---

## 6. Bearing, Distance, and Waypoint Logic
1. **Compute Bearing**  
   - Use formula:  
     `bearing = atan2(sin Δλ * cos φ2, cos φ1 * sin φ2 − sin φ1 * cos φ2 * cos Δλ)`  
     where φ = latitude radians, λ = longitude radians.
2. **Distance (Haversine)**  
   - `a = sin²((φ2−φ1)/2) + cos φ1 * cos φ2 * sin²((λ2−λ1)/2)`  
   - `distance = 2r * atan2(√a, √(1−a))` with r = Earth radius (~6371 km).  
   - Display distance remaining; trigger “Arrived” when < 5 m.
3. **Waypoint Progression**  
   - Decode polyline into list of coordinates.  
   - Set current target waypoint; when user within threshold (e.g., 5 m) advance to next.  
   - Use Directions `steps[].html_instructions` for HUD text (“Head south toward Admin Office”).

---

## 7. Rendering in AR
1. **Anchor Placement**  
   - Place an arrow model 3–5 m ahead along the bearing relative to device pose (`translation = Vector3(0, 0, -distance)`).  
   - Rotate arrow to match bearing using quaternion math (plugin usually offers helper).  
2. **HUD Overlay**  
   - Show destination name, distance, ETA, next instruction.  
   - Provide buttons: `Recenter`, `Toggle Map`, `Open Google Maps`.
3. **Breadcrumbs (Optional)**  
   - Drop small markers along polyline points so user sees the path.
4. **Arrival State**  
   - When final waypoint reached, show completion dialog and allow user to capture proof (optional photo).

---

## 8. Error Handling & Fallbacks
- **Low Accuracy**: Display warning; encourage user to move to open space.  
- **Session Lost**: Prompt to restart AR or switch to 2D map.  
- **API Failures**: Use cached POIs/routes; if unavailable, open Google Maps link directly.  
- **Unsupported Device**: Detect at launch and hide AR mode, default to map navigation.

---

## 9. QA & Handover Checklist
- [ ] Tested on at least one ARCore device outdoors on campus.  
- [ ] Verified fallback deep link works.  
- [ ] Confirmed Laravel API returns correct POI data.  
- [ ] Validated Directions quota usage; set alerts.  
- [ ] Documented limitations (Android-only, outdoor use).  
- [ ] Added README section referencing this playbook.  
- [ ] Captured screenshots/video for tomorrow’s presentation.

---

## 10. Stretch Goals (After Submission)
- iOS ARKit support.  
- Indoor positioning (BLE beacons or VPS).  
- Persistent anchors for frequently used paths.  
- Voice guidance synced with AR overlay.  
- Auto-scaling backend job to precompute routes nightly.

This playbook ensures every required step—from APIs to pose math—is covered so the AR navigation feature can be built quickly and explained clearly in project documentation.



