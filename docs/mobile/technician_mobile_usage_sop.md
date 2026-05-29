# SOP-09: Technician Mobile Usage Procedures

## 1. Purpose
This SOP standardizes the mobile device sync procedures, offline job cards tracking, and push notification configurations for workshop technicians using the mobile application.

## 2. Scope
This SOP covers the Flutter-based technician mobile application, API endpoint sync queues, push alerts, and offline storage synchronization.

## 3. Roles & Responsibilities
- **Technician**: Executes job cards, logs labor tasks, updates offline data, and uploads inspection photos.
- **Workshop Supervisor**: Monitors technician availability, audits task completion, and resolves sync disputes.

## 4. Preconditions
- Technician mobile account activated and assigned to a branch.
- Device running supported Android/iOS versions with active push services.
- Local mobile SQLite database initialized.

## 5. Step-by-Step Procedures
1. **Intake and Job Sync**:
   - Technician logs in at shift start.
   - App pulls active job assignments using:
     `GET /api/v1/mobile/staff/work-orders`
2. **Offline Mode Operation**:
   - When entering un-networked bays, the app caches task progression state (e.g. `started`, `completed`) in the local SQLite storage.
3. **Queue Syncing**:
   - Once network is restored, the app dispatches cached mutations using retry-safe API endpoints:
     `POST /api/v1/mobile/sync`
4. **Inspection Photo Upload**:
   - Upload vehicle condition images. Retry uploads if network packets drop during dispatch.

## 6. Failure Recovery Steps
- **Offline Data Sync Error**: Clean up corrupted local cache storage, force sync from remote server, and alert supervisor of local database reset.
- **Push Notification Stalls**: Re-register the Firebase Cloud Messaging (FCM) device token via account settings.

## 7. Escalation Rules
- **P2 Incident (Total sync engine failure affecting all technicians)**: Escalate to the Mobile Lead Developer.
- **P3 Incident (Delayed push alerts)**: Alert the System Administrator.

## 8. Verification Checklist
- [ ] Job status matches between the mobile client and central ERP dashboard.
- [ ] Task completion changes trigger system notifications.
- [ ] Offline job state updates sync accurately without duplicating transactions.

## 9. Rollback / Recovery Procedures
1. If the mobile app version is unstable, instruct technicians to roll back to the previous stable APK/IPA version.
2. Purge device cache folders and re-authenticate user credentials.

## 10. Audit Notes
- Every mobile sync execution, API payload hash, FCM token change, and photo upload action is logged in the system activity database logs.
