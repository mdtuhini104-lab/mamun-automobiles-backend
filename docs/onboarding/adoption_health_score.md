# Adoption Health Score Framework

This document outlines the metrics, weightings, and score classifications used to monitor tenant and user adoption health inside Mamun Automobiles ERP.

---

## 1. Score Calculation Formula

The `AdoptionHealthScore` is computed as a weighted average out of 100 points:

| Metric Category | Weight | Description |
| --- | --- | --- |
| **Daily Usage** | 30% | Frequency of logins and session active durations per user. |
| **Workflow Completion** | 25% | Rate of Job Cards successfully transitioned from creation to delivery. |
| **Onboarding Completion** | 20% | Status of initial setup steps (employees assigned, parts imported). |
| **Customer Portal Engagement** | 15% | Customer quotation views, approvals, and invoice downloads. |
| **Support Dependency** | 10% | Inverse ratio of support tickets per active operational user. |

---

## 2. Adoption Score Categories

- **Excellent Adoption (85 - 100)**: Active daily operations, fast workflow completions, minimal support tickets, high portal conversion rates.
- **Moderate Adoption (60 - 84)**: Regular weekly usage, minor workflow lags, occasional support queries.
- **At-Risk Adoption (0 - 59)**: Low daily active usage, high onboarding abandonment rates, repeated support ticket submissions.

---

## 3. Active Tenant Scorecard: Week Ending 2026-05-29

### Tenant #1: Dhaka Central Workshop
- **Daily Usage**: 28/30 (Logins verified for all staff on shift).
- **Workflow Completion**: 22/25 (Average job card cycle completed under 4.5 hours).
- **Onboarding Completion**: 20/20 (Initial parts catalogs and customer imports complete).
- **Customer Portal Engagement**: 12/15 (88% quotation approval conversion rate).
- **Support Dependency**: 9/10 (Only 1 ticket opened for minor CSV header help).
- **Overall Adoption Health Score**: **91/100 (Excellent Adoption)**

### Tenant #2: Chittagong Branch Workshop
- **Daily Usage**: 22/30 (Manager and supervisor active; technicians validating mobile views).
- **Workflow Completion**: 18/25 (Average job card cycle completed under 6 hours).
- **Onboarding Completion**: 18/20 (Customer profiles pending minor ledger reconciliation).
- **Customer Portal Engagement**: 10/15 (Quotation approvals tracking matches targets).
- **Support Dependency**: 8/10 (2 tickets opened for mobile sync help).
- **Overall Adoption Health Score**: **76/100 (Moderate Adoption)**
