# Changelog

All notable changes to the Mamun Automobiles ERP platform will be documented in this file. This project adheres to Semantic Versioning.

---

## [2.8.0] - 2026-05-31

### Changed
- **Professional Reorganized Sidebar Navigation**: Structured the sidebar menu inside `DashboardLayout.vue` into six explicit operational blocks: OPERATIONS, CUSTOMERS, FINANCE, WORKFORCE, REPORTS, SYSTEM, and a dedicated hidden SUPER ADMIN AREA.
- **Role-Based Visibility Enforcement**: Gated administrative, telemetry, and system-level actions strictly behind `Super Admin` and `Manager` roles. Cashiers/Frontdesk are restricted to Intake, Customers, Quotations, and QC & Delivery Handover. Technicians are restricted to Assigned Jobs, Parts Usage, and Technician Tasks.
- **CRM & Appointment Integration**: Merged CRM bookings and confirmation grids as a tabbed section inside Customer Directory (`CustomerList.vue`), using `useCrmStore`.
- **Bay Allocation Consolidation**: Integrated the live Workshop Bay Occupancy grid and allocation selectors as a tabbed view inside Work Order Command Center (`WorkOrderCommandCenter.vue`).
- **Accounts & Transactions Consolidation**: Merged Transaction Ledger history and Income/Expense logging action drawers as a tabbed view under Accounts (`AccountList.vue`).
- **Unified AI Operations Center**: Consolidated advanced explainable AI intelligence panels, model telemetry configurations, and anomalous incident centers under a unified AI Dashboard (`AiDashboard.vue`).
- **Direct Operational Chaining**: Linked the intake workflow end-to-end (Intake redirects to Inspection -> Inspection redirects to Diagnosis -> Approvals redirect to Work Orders -> Parts log links to QC Handover -> Settled Invoices link to Delivery Handover) to eliminate operational dead-ends.

## [2.7.0] - 2026-05-31

### Added
- **Full Operational Sidebar Navigation**: Fully exposed all 13 workshop operations routes under "Workshop Operations" with touch-friendly spacing and unique icons to ensure real staff can operate without hidden workflows.
- **Dynamic Routing & Fallback Selectors**: Configured Vue Router parameters (`/:id?`) to be optional, building and integrating `WorkspaceJobSelector.vue` to allow staff to search and select vehicles at any stage when loading a workspace directly.
- **Dedicated OBD Diagnosis Center**: Developed `DiagnosisCenter.vue` featuring live virtual OBD parameters, misfire/fuel trim check panels, diagnostic DTC code mappings (P0300, P0171), and a plan builder chaining directly to the Quotation stage.
- **Reactive 8-Column Kanban Live Board**: Redesigned `WorkshopLiveBoard.vue` into a horizontal Kanban board tracking Pending, Inspection, Quotation, Approval, Repair, QC, Ready Delivery, and Delivered columns with Laravel Reverb WebSocket syncing.
- **Customer-Facing Tracking Stepper**: Created `CustomerWorkflowTracker.vue` displaying a premium 5-stage milestone tracker (Approval Pending, Work Started, QC Running, Invoice Ready, Ready for Delivery) with task status checklist.
- **Bay Occupancy Telemetry Counters**: Added real-time counters for Busy Bays, Idle Bays, Waiting Vehicles, QC Occupied Bays, and Delayed Vehicles inside `BayOperationsBoard.vue`.
- **Mobile Offline Queue Indicator**: Added a local storage sync queue tracker badge (`mamun_offline_queue`) and media capture simulation triggers on `TechnicianMobileTaskScreen.vue` for touch-friendly field operations.

## [2.6.0] - 2026-05-31

### Added
- **Unified Job Details Layout Wrapper**: Created a premium `JobDetailsLayout.vue` wrapper and integrated it across all 12 operational workspaces to ensure technician, bay, customer, vehicle plate, and current status are visible.
- **Visual Stage Lifecycle Stepper**: Deployed `WorkflowLifecycleTracker.vue` at the top of all workspaces to track step-by-step progress (Intake -> Closed) dynamically.
- **Interactive Quotation Customizations**: Added discount/tax editable input fields, auditable revision justification modals, customer-dispatch SMS/email notification actions, and authenticated PDF download stream triggers.
- **End-to-End Operational Lifecycle Simulator**: Created `simulate_workflow.php` to simulate all 10 stages from check-in to delivery, validating parts consumption calculations, ledger adjustments, and invoice totals.

### Fixed
- **Endpoint Lookup Corrections**: Changed `/staff` to `/workforce/employees` in `WorkOrderCommandCenter.vue`, and `/crm/customer-ledger/{id}` to `/customer-ledgers/{id}` in `InvoiceSettlementWorkspace.vue`.
- **Customer Ledger stdClass Method Error**: Rewrote `getOrCreateLedger` in `CustomerLedgerService.php` to return a clean PHP anonymous class wrapper, resolving undefined `update()` exceptions during payment tracking.
- **Undefined Relationship Eager Loading**: Fixed invalid `'tasks.assignments.employee.user'` reference in `JobCardRepository.php` to target `'tasks.taskAssignments.employee.user'` correctly.
- **JobCard Eager Loading Alias**: Added a backward-compatible `workflowHistory` relation alias to `JobCard.php` to prevent relation not found exceptions during invoice compilations.
- **Vite CSS Decimal Class Compiler Crash**: Renamed invalid CSS classes `.w-9.5` and `.h-9.5` to `.w-9_5` and `.h-9_5` to resolve lightningcss minifier syntax compilation failures.

---

## [2.5.0] - 2026-05-29

### Added
- **Executive Governance Engine**: Registered `/api/v1/system/operations/executive-governance` authenticated endpoint to return comprehensive branch readiness scores, risk forecasts, cashflow trends, and markdown briefings.
- **Strategic Risk Forecasting**: Computes predictive metrics for technician overload escalation risk, burnout trends, supervisor dependency growth, and profitability degradation severity.
- **Branch Expansion Readiness & Maturity Classifier**: Evaluates and computes an Operational Maturity Score (0-100) per branch, classifying maturity levels into `Expansion Ready`, `Stable`, `Needs Optimization`, and `High Governance Risk`.
- **Workforce Sustainability Engine**: Tracks overload persistence (active workload duration), technician recovery rates (recovering operators), workload imbalances, and routing fairness standard deviation.
- **Burnout Forecast Protection**: Automatically elevates overall briefing severity to `HIGH` and triggers sustainability suggestions if average technician burnout scores or fatigue indexes remain high over consecutive weekly windows.
- **Strategic Decision Recommendations**: Compiles suggestions for invoicing restructuring, staffing redistributions, and approved branch templating candidate scalability.

---

## [2.4.0] - 2026-05-29

### Added
- **Autonomous Enterprise Observation Engine**: Deployed `/api/v1/system/operations/executive-observation` unified endpoint generating comparative branch performance, AI recommendation accountability logs, and degradation alerts ranked by severity.
- **Cross-Branch Normalized Comparative Intelligence**: Implemented comparative analysis using branch normalization metrics (revenue per technician, jobs per bay, conversions per active customer, interventions per 100 jobs, burnout risk per technician, and congestion ratio per bay) to prevent sizing bias.
- **Dynamic Executive Degradation Detection**: Scans and ranks operational alerts (INFO, WARNING, HIGH, CRITICAL) for revenue leakage, burnout risk, bay congestion, supervisor dependency, and customer hesitation.
- **Automated Executive Summary Generation**: Builds a structured markdown report containing headers for `# Executive Summary`, `## Branch Comparisons`, and `## Operational Degradation Findings` for direct executive triage.
- **Strategic Recommendation Generator**: Generates actionable plans for staffing, scheduling, workflow simplification, burnout recovery, congestion mitigation, and revenue protection.

---

## [2.3.0] - 2026-05-29

### Added
- **Continuous Optimization Engine**: Registered `/api/v1/system/operations/continuous-optimization` unified endpoint calculating WoW and MoM trend comparisons for gross/net revenue, quotation conversion rates, branch efficiency, technician efficiency, and burnout risk indexes.
- **AI Recommendation Drift Protection**: Implemented dynamic calculations checking if approved recommendation usefulness is continuously declining over a rolling 14-day window. Automatically dampens confidence scores by `0.80`, increases explainability visibility, raises supervisor review guidelines, and logs P3 quality alerts.
- **Workflow Bottleneck & Suggestion Engine**: Deployed scans that detect repeatedly congested bays, tech overload cycles, stalled quotations (>2h sent with no action), delayed approvals, and technician idle spikes, generating targeted resolution recommendations.
- **Revenue Leakage Security Audits**: Enabled audits for unbilled completed job cards and estimate underbilling leakage, classifying issues into LOW (<5%), MEDIUM (5% to 15%), and HIGH (>15%) leakage severity levels.
- **Customer Trust Intelligence**: Tracks customer hesitation scores, quotation abandonment trends, delayed payment ratios, and comeback trust impact metrics.

---

## [2.2.0] - 2026-05-29

### Added
- **Operational Excellence KPI Engine**: Deployed `/api/v1/system/operations/excellence-kpis` unified dashboard endpoint exposing branch efficiency, technician efficiency, repeat customer rates, delayed workflow ratios, and support dependency trends.
- **AI recommendation quality metrics**: Implemented the Recommendation Usefulness Score, Operational Trust Index, and AI Coordination Stability Index to audit system trustworthiness and prevent rogue routing.
- **Precision Quotation Conversion Rates**: Added a 5-minute threshold filter to exclude supervisor-cancelled drafting corrections from quotation conversion analytics.
- **Multi-Tenant Gross & Net Revenue Tracker**: Enabled tracking of gross revenue (including pending/unpaid invoices) and net revenue (paid invoices only) per technician and per bay.
- **Comeback Job Frequency Sweeps**: Integrated scans identifying repeat workshop visits within a 30-day window for the same vehicle.
- **Realtime Execution Telemetry**: Exposed websocket stability, offline sync latency, queue recovery duration, technician idle ratio, and supervisor intervention frequency.

---

## [2.1.0] - 2026-05-29

### Added
- **Frontdesk Quick-Load Optimization**: Created `GET /api/v1/system/operations/customer-quick-load` supporting under-50ms repeat customer loading via cache-backed vehicle, invoice count, and negotiation history preloading.
- **Negotiated Pricing Auto-Fills**: Integrated fallback pricing structure logic that preloads the customer's last approved negotiated pricing structure, with automatic tier-based fallback (VIP: 5% relationship discount, Corporate: 10% volume discount).
- **Mobile Idempotency Protections**: Applied `idempotent` middleware checking `X-Idempotency-Token` headers to mobile task status updates (`PUT /mobile/tasks/{id}/status`) preventing duplicate swipe, sync, or rapid touch conflicts.
- **Supervisor Command Console**: Developed `/system/operations/supervisor-dashboard` telemetry feed showing real-time bay congestion counts, delayed task visibility, QC validation backlogs, and technician overload indicators.
- **Supervisor Escalation Policy**: Implemented rules where supervisor QC validation delays >30 mins trigger HIGH alerts on the dashboard, and delays >60 mins trigger P2 escalation alerts.
- **Webhook Replay Safeguards**: Implemented unique signature-based signature validation and duplicate event checks in POST `/system/operations/webhook-receiver`, rejecting duplicate transactions with 409 Conflict.
- **Explainable AI Recommendation Telemetry**: Enhanced `/system/operations/load-balancing` payloads with structured `explainability` and `safety_corridor` arrays revealing base score, calibration multipliers, and safety thresholds.

## [2.0.0] - 2026-05-29

### Added
- **Human Override Feedback Loops**: Registered `POST /api/v1/system/operations/recommendations/{id}/action` allowing supervisors to override, approve, or reject AI suggestions with custom feedback notes and effectiveness ratings.
- **Reinforcement Learning Calibration**: Configured dynamic calibration of future recommendation confidence scores based on historical average effectiveness, with multiplier bounds between 0.70 and 1.15.
- **AI Coordination Simulations**: Added a sandboxed, read-only simulation endpoint `POST /api/v1/system/operations/simulate` projecting throughput and burnout implications of bay redistribution and technician reassignment.
- **Self-Tuning Operational Baselines**: Implemented dynamic calculations of stalled workflow limits (rolling average of task completion time) and technician overload thresholds.
- **Operational Learning Metrics Dashboard**: Created `GET /api/v1/system/operations/learning-metrics` to expose supervisor acceptance rates, burnout recovery success rates, and calibration multipliers.
- **Audit Trails**: Registered audit logs recording all supervisor override decisions.

### Security & Operational Rules
- Enforced strict read-only guarantees on the simulation engine to prevent database mutations or live alerting triggers.
- Constrained confidence scores to a safe operational corridor (50.0% floor to 99.0% ceiling).

## [1.9.0] - 2026-05-29

### Added
- Implemented **Adaptive Operational Intelligence & Load Balancing Suggestions** endpoint (`GET /api/v1/system/operations/load-balancing`) returning confidence score telemetry and detailed operational reasoning arrays (e.g., workload imbalance, technician overload).
- Added **Workforce Sustainability & Burnout Recovery Protection** recommendations proposing rest recovery periods, workload redistribution, reassignment suggestions, and temporary cooldown locks.
- Integrated **Adaptive Anomaly Severity Classifications** (`GET /api/v1/system/operations/anomalies`) segmenting issues into Critical (workshop blocking), High (escalation overdue), Medium (technician overload), and Low (temporary delays).
- Added **Operational Trust Score** algorithm and analytics insights dashboard (`GET /api/v1/system/operations/adaptive-analytics`).
- Extended `ai_recommendations` table to persist operational outcome tracking and effectiveness scores.

## [1.8.0] - 2026-05-29

### Added
- Created `predictive_snapshots` database schema and model for periodic operational snapshot storage.
- Added `actual_minutes` column to the `job_card_tasks` table for tracking exact task completion durations.
- Implemented `GET /api/v1/system/operations/predictive-metrics` endpoint, returning the standardized `FlowEfficiencyScore` segmented by branches, departments, technicians, and bays.
- Implemented `POST /api/v1/system/operations/trigger-escalations` endpoint, executing scans for stalled workflows, mapping alerts to P1-P4 priority tiers with safety boundaries and 30-minute cooldown protections.
- Integrated **Intelligent Routing Protection** inside the workshop suggestions engine, calculating a composite routing suitability score using workload fairness, comeback ratios, fatigue indicators, and specialization.

### Security & Operational Rules
- Enforced auto-escalation safety policies: closed, paused, and archived records are strictly excluded from escalation sweeps.
- Deployed minimum 30-minute cooldown protection gates to prevent noisy alert storms for duplicate events.

## [1.7.0] - 2026-05-29

### Stability Improvements
- Established the `delay_heatmaps.md` tracker logging stage transition latencies and isolating supervisor/technician operational response delays.

### UX Refinements
- Upgraded mechanic mobile task updates to use swipe triggers (swipe left to complete, swipe right to start) to replace button click friction.
- Deployed supervisor bay congestion alarms when active queues exceed 3 vehicles in a single bay.

---

## [1.6.0] - 2026-05-29

### Stability Improvements
- Integrated `time_to_completion.md` tracking speeds for Job Card creation, quotation approval, task assignment, invoice generation, and average job completions.

### UX Refinements
- Optimized frontdesk screens with cache-backed customer lookup auto-suggests.
- Reworked mechanic mobile boards with larger touch interface grids and a minimal forms layout.
- Deployed supervisor visibility dashboard additions showing blocked workflow alerts and workload balancing metrics.

---

## [1.5.0] - 2026-05-29

### Stability Improvements
- Established the `AdoptionHealthScore` framework (`docs/onboarding/adoption_health_score.md`) calculating weighted averages for Daily Usage, Workflow Completion, Onboarding Completion, Customer Portal Engagement, and Support Dependency.

### UX Refinements
- Restructured `staff_friction_heatmap.md` to track click-reduction metrics (clicks before vs clicks after) and mapped usability patterns separately across 5 staff roles.
- Enhanced `weekly_executive_summary.md` to track the customer conversion funnel (Quotation Sent -> Viewed -> Opened Again -> Approval Started -> Approval Completed -> Payment Started -> Payment Completed) to isolate hesitation trends.

---

## [1.4.0] - 2026-05-29

### Stability Improvements
- Deployed daily, weekly, and monthly monitoring checklists under a real workshop pilot deployment environment.
- Created `weekly_executive_summary.md` tracking system incidents and alert threshold limits.

### UX Refinements
- Drafted a `staff_friction_heatmap.md` tracking user abandonment rates, repeated clicks, and mobile UI usability pain points to guide future interface improvements.

### Operational Optimizations
- Deployed a `pilot_branch_tracking.md` sheet monitoring onboarding metrics, active staff configurations, and pain points across active branches (Dhaka Central and Chittagong).

---

## [1.3.0] - 2026-05-29

### Operational Changes
- Standardized operational SOP directory structures for onboarding, deployment, incidents, and support management.
- Logged first weekly operational review metrics covering Horizon queues, websocket uptime, and client onboarding metrics.
- Added live Incident Timelines log (`docs/incident-response/incident_timelines.md`) and Recovery Drills validation sheets (`docs/operations/recovery_drills.md`).

### Infrastructure Updates
- Added weekly/monthly simulation schedules for GPG-encrypted database backup recovery audits.

### Workflow Optimizations
- Integrated suggestions engine autocompletes for technician complaint templates and replacement parts suggestions.

### Bug Fixes
- Fixed nested middleware rate limit signature key collision issues inside the API routing configurations.

### Deployment Adjustments
- Authored detailed deployment SOP instructions detailing Horizon queue restarts, config cache flushes, and database migration safety check procedures.

---

## [1.2.0] - 2026-05-29
### Added
- Gated Stress endpoints supporting simulated slow query (`/system/stress/slow-query`), failed job (`/system/stress/failed-job`), and websocket drop (`/system/stress/websocket-disconnect`).
- Composite reporting database indexes on `job_cards`, `invoices`, and `audit_logs` to optimize analytics performance.
- Automated subscription renewal daily alerts (`saas:send-renewal-reminders`) with 24h duplicate protections and opt-out caching.
- Customer portal activity and inactivity trackers with 15-minute, 1-hour, 24-hour, and 72-hour segmentation steps.
- Workflow Auto-Suggestions engine for mechanics, bays, complaints, and parts recommendations (recommendation-only).

### Fixed
- Throttling collisions on debug stress endpoints by applying unique prefix signatures (`throttle:5,1,stress`).

---

## [1.1.0] - 2026-05-27
### Added
- Horizon and failed queue list cockpit with Super Admin role gates.
- Tenant health snapshots recording scheduler (`saas:record-health-snapshots`) and retention alert automation.
- Support ticket similarity search engine based on title keywords.
- 30-day rolling performance telemetry logs for slow queries and queue latencies.

### Fixed
- Cross-branch request leak guards using `BranchMiddleware` checks.

---

## [1.0.0] - 2026-05-20
### Added
- Initial Multi-Tenant Production Release.
- Stripe payment gateway integration.
- Vue dynamic dashboard and mobile API services.
