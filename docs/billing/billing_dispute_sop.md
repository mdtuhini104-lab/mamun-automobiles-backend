# SOP-06: Billing Dispute Procedures

## 1. Purpose
This SOP establishes standard procedures to investigate, resolve, and audit payment gateway discrepancies, billing disputes, and ledger mismatches between Stripe/bKash and Mamun Automobiles ERP.

## 2. Scope
This SOP covers multi-tenant subscriptions billing, public gateway webhook events (Stripe/bKash), cashbook registers, customer ledgers, and invoice balance corrections.

## 3. Roles & Responsibilities
- **Finance Officer**: Investigates customer accounts, checks transaction histories, and drafts refunds/adjustments.
- **Super Admin (Administrator)**: Approves refunds, adjusts database ledgers, and overrides Stripe webhook states.

## 4. Preconditions
- Access to the Stripe Merchant Dashboard and bKash transaction reports.
- Access to the customer ledgers database table.
- Credentials to view and edit payments logs.

## 5. Step-by-Step Procedures
1. **Receive Billing Dispute**:
   - Customer or tenant submits a ticket containing a payment/invoice discrepancy details.
2. **Audit Transaction Records**:
   - Match the ERP payment log ID against the transaction reference in Stripe or bKash dashboards.
   - Verify if the payment status is marked `succeeded` or `failed` in the gateway logs.
3. **Inspect Gateway Webhooks**:
   - Check if the webhook event failed to execute or replay.
   - If a webhook event failed to deliver, replay the event from the Stripe Developer Dashboard.
4. **Resolve Ledger Mismatches**:
   - If the payment was successful in the gateway but did not update the ERP, manually trigger invoice collection updates in the backend console:
     ```bash
     php artisan billing:collect-invoice <invoice-id> --payment-ref=<transaction-ref>
     ```
5. **Issue Refund**:
   - If a refund is required, process the refund through the Stripe/bKash Dashboard first.
   - Record the refund amount and corresponding audit notes in the ERP customer ledger to reconcile balances.

## 6. Failure Recovery Steps
- **Gateway API Offline**: Log the action as `pending_gateway_sync` and schedule a manual sync retry in 30 minutes.
- **Ledger Correction Error**: Revert the manual adjustment immediately, log the SQL database error, and notify the DB Admin.

## 7. Escalation Rules
- **P1 Incident (Stripe cross-tenant billing mix-up)**: Escalate to the CEO and Legal team immediately.
- **P2 Incident (Payment webhook delivery outage > 2 hours)**: Escalate to the DevOps Lead.

## 8. Verification Checklist
- [ ] ERP customer ledger matches the Stripe charge invoice balance.
- [ ] Invoice status is updated to `paid` or `refunded` accordingly.
- [ ] Database log contains the corresponding GPG-validated refund signature.

## 9. Rollback / Recovery Procedures
1. To roll back an incorrect ledger adjustment, run the ledger reconciliation command:
   ```bash
   php artisan billing:reconcile-customer-ledger <customer-id> --dry-run=false
   ```
2. Re-assert that no duplicate cashbook transactions were generated.

## 10. Audit Notes
- Every billing adjustment, discount, refund, or ledger override must contain the operator signature, approval timestamp, dispute reason, and linked ticket ID in `audit_logs`.
