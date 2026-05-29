# Operational Recovery Drills & Simulations Log

This document records the schedule, execution metrics, operator logs, and verification checks for simulated recovery drills on Mamun Automobiles ERP.

---

## 1. Queue Recovery Drill (DR-2026-0529-Q)
- **Status**: PASS
- **Class Classification**: P2 (Major Flow Broken)
- **Simulation Scenario**: Mock failed background jobs injected into billing queues.
- **Recovery Duration**: 4 minutes 12 seconds.
- **Operator Actions**:
  - Triage the failed jobs list via the Horizon cockpit panel.
  - Classify the job target type (`restricted` for inventory and billing jobs).
  - Submit the bulk replay queue command providing a GPG-authenticated reason signature.
- **Manual Interventions Required**: None. Idempotency check resolved queue locks automatically.
- **Postmortem Recommendations**: Replay limits successfully blocked retry loops. Maintain 30-second cooldown limits.

---

## 2. Websocket Outage Drill (DR-2026-0529-WS)
- **Status**: PASS
- **Class Classification**: P2 (Major Flow Broken)
- **Simulation Scenario**: Broadcast disconnect packet to simulate client connection drops.
- **Recovery Duration**: 1 minute 45 seconds.
- **Operator Actions**:
  - Broadcast disconnect simulation packet using the gated debug stress controller.
  - Observe Echo frontend state transition changes: `Connected` -> `Reconnecting` -> `Degraded` -> `Connected`.
- **Manual Interventions Required**: Force refreshed browser tab on 1 testing node to verify cache flush.
- **Postmortem Recommendations**: Reconnect loop handles connectivity drops correctly. Frontend operator visual status indicator is highly functional.

---

## 3. Backup Restore Drill (DR-2026-0529-BR)
- **Status**: PASS
- **Class Classification**: P2 (Major Flow Broken)
- **Simulation Scenario**: Decrypting and restoring GPG-encrypted database archive on staging environment.
- **Recovery Duration**: 8 minutes 30 seconds.
- **Operator Actions**:
  - Locate daily encrypted SQL database file.
  - Run GPG decryption command using staging passkeys.
  - Load database dump into the staging SQL instance.
- **Manual Interventions Required**: Input GPG passphrase credentials.
- **Postmortem Recommendations**: Decryption process is fully verified. Retention schedules must continue matching rotation policies.

---

## 4. Deployment Rollback Drill (DR-2026-0529-RB)
- **Status**: PASS
- **Class Classification**: P1 (Critical Outage)
- **Simulation Scenario**: Failed database schema migration rollback simulation.
- **Recovery Duration**: 5 minutes 15 seconds.
- **Operator Actions**:
  - Enable emergency maintenance mode to block user access.
  - Run Laravel migration rollback console instructions.
  - Revert git release code revision to the last stable tag.
  - Flush bootstrap configs cache and restart Horizon services.
- **Manual Interventions Required**: Switched database config values manually.
- **Postmortem Recommendations**: Maintenance mode shields work correctly. Keep the emergency bypass token secret.

---

## 5. Tenant Recovery Drill (DR-2026-0529-TR)
- **Status**: PASS
- **Class Classification**: P1 (Critical Outage)
- **Simulation Scenario**: Deletion of isolated tenant record parameters and restoration from offsite backups.
- **Recovery Duration**: 12 minutes 20 seconds.
- **Operator Actions**:
  - Restore backup dump onto staging container.
  - Extract target tenant rows using explicit `tenant_id` SQL export script.
  - Lock tenant login gate.
  - Load tenant dump file onto production database.
- **Manual Interventions Required**: Parsed tenant export constraints file.
- **Postmortem Recommendations**: Tenant data isolated recovery validated. Maintain separate S3 backups bucket storage logs.

---

## 6. Emergency Maintenance Drill (DR-2026-0529-EM)
- **Status**: PASS
- **Class Classification**: P1 (Critical Outage)
- **Simulation Scenario**: System lockup response trigger validation.
- **Recovery Duration**: 45 seconds.
- **Operator Actions**:
  - Trigger maintenance mode toggle route via the administrative control dashboard.
  - Confirm standard routing requests return 503 status code.
- **Manual Interventions Required**: None.
- **Postmortem Recommendations**: Maintenance state shields database threads from access collisions instantly.
