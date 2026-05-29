# SOP-04: Queue Failure Recovery Procedures

## 1. Purpose
This SOP outlines procedures to diagnose, classify, and replay failed background queue jobs within Mamun Automobiles ERP, ensuring queue reliability and data consistency.

## 2. Scope
This SOP applies to Laravel queue connections, Horizon background runners, Redis datastores, and scheduled command queues.

## 3. Roles & Responsibilities
- **Super Admin (Operations Administrator)**: Authorized to run failed job retries, delete failed jobs, and execute bulk queue replays.
- **Operations Manager**: Reviews failed job logs and tracks performance alerts.

## 4. Preconditions
- Access to the queue performance telemetry dashboard `/api/v1/system/performance/telemetry`.
- Administrative rights on the Laravel console.
- Verification checks for Redis connection availability.

## 5. Step-by-Step Procedures
1. **Monitor Queue Latency**:
   - Check queue lag and processing delays in telemetry reports. If latency exceeds 5 seconds, begin troubleshooting.
2. **Review Failed Jobs List**:
   - Query the failed jobs database table to inspect the traceback details:
     ```bash
     php artisan queue:failed
     ```
3. **Classify Failed Jobs**:
   - **Safe Replay**: (Notifications, emails, PDF generations). These are non-mutating and can be retried immediately.
   - **Restricted Replay**: (Payments, invoices, stock adjustments). Require idempotency confirmation before retrying to prevent double mutations.
4. **Acquire Idempotency Lock**:
   - Ensure a 10-second lock exists on the target job ID before execution to avoid race conditions.
5. **Execute Replay**:
   - For safe jobs:
     ```bash
     php artisan queue:retry <job-id>
     ```
   - Record the audit reason (minimum 5 characters) for every retry action.

## 6. Failure Recovery Steps
- **Queue Thread Lockup**: Terminate Horizon processes and let Supervisor reboot them:
  ```bash
  php artisan horizon:terminate
  ```
- **Redis Out of Memory**: Flush expired cache holds to free RAM:
  ```bash
  redis-cli FLUSHDB
  ```

## 7. Escalation Rules
- **P2 Incident (Queue failure > 100 jobs)**: Escalate to the Infrastructure Team.
- **P3 Incident (Queue latency > 30 seconds)**: Notify the Lead Architect.

## 8. Verification Checklist
- [ ] Redis command `ping` returns `PONG`.
- [ ] Laravel Horizon status is active and processing.
- [ ] Database ledger balances match after restricted job retry executes.
- [ ] Telemetry queue latency drops below 2 seconds.

## 9. Rollback / Recovery Procedures
1. If a replayed job triggers database discrepancies, immediately suspend the queue:
   ```bash
   php artisan queue:clear
   ```
2. Manually adjust the database ledger records using SQL recovery transaction scripts.

## 10. Audit Notes
- Every failed job execution, retry count, operator ID, and replay reason must be logged in `audit_logs` for compliance auditing.
