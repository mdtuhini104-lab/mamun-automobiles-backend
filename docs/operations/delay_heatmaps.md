# Workflow Delay Heatmaps

This document records the operational delays, stage transition latencies, and repeated bottlenecks mapped across the repair lifecycles in Mamun Automobiles ERP.

---

## 1. Stage Transition Latency Breakdown

We analyze the duration spent in each operational state to isolate latency:

| State Transition Stage | Target Duration | Average Actual | Delay Impact |
| --- | --- | --- | --- |
| **`pending_inspection`** to **`inspected`** | < 1.0 hour | 45 minutes | Low |
| **`inspected`** to **`quotation_draft`** | < 30 minutes | 18 minutes | Low |
| **`quotation_draft`** to **`quotation_approved`** | < 2.0 hours | 12 minutes | Low (Accelerated by Portal) |
| **`work_order_active`** to **`ready_for_qc`** | < 3.0 hours | 2.4 hours | Medium (Task execution) |
| **`ready_for_qc`** to **`ready_for_delivery`** | < 30 minutes | 42 minutes | **High (Supervisor QC delay)** |
| **`ready_for_delivery`** to **`closed`** | < 30 minutes | 15 minutes | Low |

---

## 2. Repeated Operational Bottlenecks

We track the slowest operational steps and their corresponding mitigation progress:

### Supervisor Quality Control Validation Delay
- **Observed Bottleneck**: Supervisors often take over 40 minutes to verify repairs after technicians mark them complete.
- **Root Cause**: Supervisors were out road-testing other vehicles and missed completion updates.
- **Mitigation Action**: Deployed real-time push alerts and live workload balancing views in the supervisor dashboard.

### Slowest Technician Response Chains
- **Observed Bottleneck**: Technicians take on average 8 minutes to tap "Start Task" on mobile devices after assignment.
- **Root Cause**: Grease-covered hands or small screen tap regions.
- **Mitigation Action**: Implemented large-button swipe-to-start task action components to reduce interaction friction.

---

## 3. High-Abandonment & Delayed Approval Analysis

### Quotation Abandonment Point
- **Highest Abandonment Stage**: Re-opened Quotation stage (14% drop-off).
- **Behavior Trigger**: Customers revisit the portal multiple times and drop off if no follow-up occurs.
- **Mitigation Action**: Deployed cache-backed activity logs tracking "Opened Again" events to trigger automated success advisor alerts.
