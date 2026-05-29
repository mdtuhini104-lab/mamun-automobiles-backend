# SOP-10: Support Escalation Procedures

## 1. Purpose
This SOP standardizes Support Ticket routing, Incident mapping, SLA targets, SLA breaches escalation, and Knowledge Base publishing inside the Mamun Automobiles ERP platform.

## 2. Scope
This SOP governs all SaaS support tickets, customer success complaints, billing disputes, and technical system incidents.

## 3. Roles & Responsibilities
- **Support Agent (Tier 1)**: Reviews incoming tickets, matches titles using similarity engine, and provides KB guides.
- **Support Engineer (Tier 2)**: Resolves complex database, workflow, and API integration issues.
- **Super Admin (Tier 3)**: Resolves security incidents, system outages, and custom infrastructure bugs.

## 4. Preconditions
- Ticket submitted in the support portal.
- Active on-call rotation registry.
- Diagnostics logs viewer access.

## 5. Step-by-Step Procedures
1. **Ticket Intake & Classification**:
   - Ticket enters status `open` in category (e.g. `billing`, `technical`, `onboarding`).
   - Run similarity engine query to search for duplicate open tickets or relevant resolved KB articles.
2. **SLA Monitoring & Escalation**:
   - Check ticket priority response targets:
     - **Urgent Priority**: First response within 15 minutes. Resolution within 2 hours.
     - **High Priority**: First response within 1 hour. Resolution within 6 hours.
     - **Medium Priority**: First response within 4 hours. Resolution within 24 hours.
     - **Low Priority**: First response within 12 hours. Resolution within 48 hours.
   - If SLA response limits are breached, trigger system alerts to the next support tier.
3. **Incident Mapping**:
   - If the root cause is a technical bug, map the ticket to an Incident entry.
4. **Knowledge Base Conversion**:
   - Once resolved, convert the incident resolution workflow steps into a dynamic KB article to enable self-service.

## 6. Failure Recovery Steps
- **KB Article Generation Fails**: Manually edit the article draft, clean formatting characters, and re-publish.
- **Ticket Assignee Offline**: Automatically re-route the ticket to the generic active support queue.

## 7. Escalation Rules
- **Urgent Ticket Breaches SLA Limit**: Escalate immediately to the Support Lead and CEO.
- **Unresolved High Tickets > 12 hours**: Re-assign directly to the Tier 3 Engineering team.

## 8. Verification Checklist
- [ ] Ticket status moves from `open` to `resolved`.
- [ ] Satisfaction score is requested from the customer post-resolution.
- [ ] KB article slug is reachable and searchable.

## 9. Rollback / Recovery Procedures
1. If a published KB guide is incorrect, revoke the article immediately:
   ```bash
   php artisan support:retract-kb-article <article-slug>
   ```
2. Re-open the linked support ticket to assign back to Tier 2 support.

## 10. Audit Notes
- Response timelines, assignee changes, escalation actions, and customer feedback surveys are logged in the `support_tickets` audit logs.
