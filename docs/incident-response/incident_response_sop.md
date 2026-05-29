# SOP-03: Incident Response Procedures

## 1. Purpose
This SOP details the identification, triage, resolution, and postmortem documentation procedures for operational incidents affecting Mamun Automobiles ERP.

## 2. Scope
This SOP covers database latency, application crashes, security violations, payment gateway issues, and mobile sync failures.

## 3. Roles & Responsibilities
- **First Responder (On-Call Engineer)**: Performs initial assessment, categorizes severity level, and initiates triage.
- **Incident Commander (Super Admin)**: Manages communications, executes mitigations, and leads postmortem meetings.
- **Support Liaison**: Communicates status updates to affected tenants.

## 4. Preconditions
- Access to the application telemetry panel `/api/v1/system/performance/telemetry`.
- Access to server logs (`storage/logs/laravel.log` and Nginx error logs).
- Operational dashboard viewing permissions.

## 5. Step-by-Step Procedures
1. **Incident Triage**:
   - Classify the incident according to these metrics:
     - **P1 (Critical Outage)**: System offline, invoice/payment system down, tenant cross-talk leak.
     - **P2 (Major Flow Broken)**: Horizon queue failure, Websocket connection unavailable, SMS gateway locked.
     - **P3 (Degraded)**: Latency > 2s, reporting dashboard timeout, notification delay.
     - **P4 (Cosmetic)**: CSS broken, formatting issue, dashboard minor alignment.
2. **Mitigation Execution**:
   - If P1: Enable emergency maintenance mode immediately.
     ```bash
     php artisan system:maintenance --enable
     ```
   - Review active system threads and slow queries:
     ```bash
     php artisan system:show-active-threads
     ```
3. **Map Incident to Support Ticket**:
   - Link support ticket details to the created incident entry for tracking.
4. **Resolution Steps**:
   - Apply hotfix patches, run migrations, or restore database backup according to standard SOPs.
5. **Knowledge Base Publishing**:
   - Export resolved workflow documentation directly to a KB article to reduce future ticket escalation.

## 6. Failure Recovery Steps
- **Hotfix Fails**: Immediately rollback the codebase to the previous Git release tag and notify the Incident Commander.
- **Log Drive Full**: Purge older system logs to free disk space:
  ```bash
  journalctl --vacuum-time=2d
  ```

## 7. Escalation Rules
- **P1 Incident**: Notify leadership if resolution time exceeds 15 minutes.
- **P2 Incident**: Escalate to the DevOps team if resolution time exceeds 30 minutes.

## 8. Verification Checklist
- [ ] System status dashboard returns green (`OK`).
- [ ] DB query latencies are below 100ms.
- [ ] End-to-end user transactions execute without throwing exceptions.
- [ ] Log outputs contain zero recurring errors.

## 9. Rollback / Recovery Procedures
1. Revert any applied hotfixes:
   ```bash
   git checkout <stable-tag>
   php artisan optimize:clear
   ```
2. Disable maintenance mode:
   ```bash
   php artisan system:maintenance --disable
   ```

## 10. Audit Notes
- Every incident requires a signed Incident Postmortem report outlining:
  - Timeline of events.
  - Root cause analysis (RCA).
  - Mitigation strategies applied.
  - Long-term preventative actions.
