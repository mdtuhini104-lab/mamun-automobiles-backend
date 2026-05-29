# Flow Efficiency & Throughput Analytics

This document records the workshop flow execution metrics, waiting times, idle durations, and throughput statistics mapped in Mamun Automobiles ERP.

---

## 1. Flow Efficiency Score Calculations

The `FlowEfficiencyScore` evaluates the percentage of time a vehicle spends receiving active service versus waiting idle in the workshop:

$$\text{Flow Efficiency} = \left( \frac{\text{Active Work Time}}{\text{Active Work Time} + \text{Waiting/Idle Time}} \right) \times 100$$

### Workshop Performance Averages: Week Ending 2026-05-29

| Branch | Active Work Time (Avg) | Waiting/Idle Time (Avg) | FlowEfficiencyScore | Status |
| --- | --- | --- | --- | --- |
| **Dhaka Central (DHK-01)** | 2.5 hours | 42 minutes | **78.1%** | **Healthy** |
| **Chittagong (CTG-01)** | 2.8 hours | 1.1 hours | **71.8%** | **Healthy** |

- **Target Threshold**: FlowEfficiencyScore > 70.0% is considered healthy.

---

## 2. Operational Throughput Analytics

We track daily cars serviced, repair cycle times, and bay utilization rates:

- **Cars Serviced Per Day**: 22 vehicles (Dhaka Central), 8 vehicles (Chittagong).
- **Average Repair Cycle Duration**: 3.2 hours.
- **Quotation Approval Delay**: 12 minutes (average).
- **Comeback/Warranty Rate**: 0.0% (Zero comeback repairs logged).
- **Bay Utilization Rate**:
  - Dhaka Central: 74% (bays occupied 5.9 hours of an 8-hour shift).
  - Chittagong: 62% (bays occupied 5.0 hours of an 8-hour shift).

---

## 3. Auto-Escalation Metrics & Alert Incidents

The system automatically triggers alerts and escalations for stalled workflows:

| Trigger Event | Threshold Limit | Action Escalated To | Incident Count (Weekly) |
| --- | --- | --- | --- |
| **Stalled Mobile Task** | Task inactive > 30 minutes | Workshop Supervisor | 2 alerts |
| **Delayed Portal Approval** | Quotation open > 2 hours | Operations Manager | 1 alert |
| **Prolonged Bay Occupation** | Bay occupied > 4 hours | Operational Coordinator | 0 alerts |
| **Overdue Invoice Payment** | Invoice generated > 4 hours | Accountant | 0 alerts |
