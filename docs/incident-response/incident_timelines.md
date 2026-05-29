# Production Incident Timelines

This document tracks all production operational incidents, their triage severity levels, detection triggers, mitigation timelines, and postmortem outcomes.

---

## 1. Incident Severity Definitions
- **P1 (Critical Outage)**: System offline, complete database lock, billing failures, tenant data isolation leaks.
- **P2 (Major Flow Broken)**: Horizon queue stall, Websocket connection server collapse, core SMS/email gateway lock.
- **P3 (Degraded)**: Latency > 2s, reporting dashboard metrics aggregation lag, delayed client alerts.
- **P4 (Cosmetic)**: Minor UI alignment discrepancies, CSS layout anomalies.

---

## 2. Active Operational Timeline Log

### Incident: INC-2026-0529-01
- **Severity**: P2 (Major Flow Broken)
- **Incident Type**: Websocket and Rate Limiter Collision
- **Detection Time**: 2026-05-29T14:37:55Z
- **Trigger**: System stress test pipeline returned `500 Internal Server Error`.
- **Response Time (Triage)**: 3 minutes.
- **Recovery Duration**: 15 minutes.
- **Root Cause**: Stacking `throttle:60,1` on the API routing group and `throttle:5,1` on individual stress testing endpoints caused a rate-limiter signature key collision, incrementing hit counts twice per request and triggering rate limiting prematurely.
- **Mitigation Actions**:
  - Appended unique key prefixes (`throttle:5,1,stress`) to the stress endpoint middlewares in the API route file.
  - Ran clean unit validation testing suites to confirm issue resolution.
- **Postmortem Summary**: The signature rate limit key must dynamically segment on parameter lengths or names when nested. Added custom rate-limiting prefix tags to all child routes.

---

### Incident: INC-2026-0527-02
- **Severity**: P3 (Degraded)
- **Incident Type**: Query Latency reporting lag
- **Detection Time**: 2026-05-27T10:14:05Z
- **Trigger**: Server monitoring alert showing DB load spikes on financial queries.
- **Response Time (Triage)**: 10 minutes.
- **Recovery Duration**: 45 minutes.
- **Root Cause**: Missing compound indexes on high-frequency tables (`job_cards`, `invoices`, `audit_logs`).
- **Mitigation Actions**:
  - Prepared and executed migrations adding composite compound database indexes matching tenant query scopes.
- **Postmortem Summary**: Composite indexes successfully reduced average query execution times from 10.4s to 0.05s.
