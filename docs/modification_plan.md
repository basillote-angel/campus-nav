# Modification Plan – NavistFind Workflow Enhancements

This document summarizes the upcoming changes requested on November 13, 2025.  
Tasks are grouped by system phase to reflect the end-to-end flow.

---

## Phase 1 – Lost Item Posting (Mobile App → API)
- **No changes requested** – current flow already posts lost items and triggers AI matching.

---

## Phase 2 – Recommendation & Claim Submission (Mobile App)
1. **Recommended Items Ordering**
   - Ensure smart recommendation sections (Home “Smart Recommendation” & dedicated Recommendations page) sort results from highest to lowest match score.
   - Confirm notifications highlight the highest-score match when a lost item is posted. i want to notify via email

2. **Claim Availability**
   - Claims keep the found item available to other users until an admin approves.

---

## Phase 3 – Admin Review & Approval (Web Dashboard)
### 3.1 Pending & Approval Workflow
- the status should be unclaimed
- in pending list claim  detail modal i want to remove the  Claim Information card since there is    claimant information is below to the Timeline card. 
- in claim details modad it should have the following : 
    item Details card, 
    timeline card, and 
    the card for the Pending Claims(where we can be able to see multiple list of claimant if there is mulltiple claimant).
    inside the card for each claimant should have the percentage of matched item base on there  posted item. and should have approve, and reject button and i want you to remove  the add notes button feature. 
    - if the admin click approve this would notify the mobile user via email of automatic system generated message  and the approve item will go to approve table.
    - if the admin click the reject button it automatically notify the system gerated message abotu the reject via email
  

- **Approved Items List**
  - Sort approved items in descending order (latest approval first).
   - should have a Manage Approval card button for (send reminder, cancel approval, mark collected)
      -if send reminder it send notification via email about the approve item they claimm.
    
     - if “Cancellation” button to revert a mistaken approval:
    - Set the found item back to `unclaimed`.
    - Make the item visible again to AI recommendations and mobile listings.
    - Notify the claimant that approval was canceled. 
    - add mark collected
- **SLA Alerts**
  - Monitor pending claims that exceed the 24-hour SLA window.
  - Notify admin/staff users and log the event for follow-up.
  - Reset SLA flags when claims are resolved, canceled, or items are reopened.
- **Status Clarification**
  - Introduce `awaiting_collection` for approved-but-uncollected found items to keep `returned` exclusively for completed hand-offs.

- **Collection Separation**
  -list of collected item lated is from the top

### 3.2 Reminder Logic
- Confirm automatic reminder runs every 3 days while item is `awaiting_collection` and not returned.


---

## Phase 4 – Notifications & Status Transitions
- **Mobile Notifications:**
  - When admin approves a claim: remind claimant to pick up item at the office.
  - When approval is canceled: inform claimant that access is revoked and item reopens to other users.

- **Found Item Visibility Rules:**
  - Approved (pending pickup) items should remain hidden from mobile search/recommendations for other users.
  - Once canceled or reopened, visibility resumes as “available”.

---

## AI Recommendation Service
- No direct changes required, but ensure reopened items (after cancellation) qualify for recommendation jobs.

---

---


