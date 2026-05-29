# SOP-12: Security Incident Procedures

## 1. Purpose
This SOP details procedures to detect, isolate, block, and document security threats, cross-tenant data leaks, brute-force attacks, and unauthorized operations in Mamun Automobiles ERP.

## 2. Scope
This SOP covers application authentication servers, Sanctum API tokens, database tenant tables, and server firewalls.

## 3. Roles & Responsibilities
- **First Security Responder (Super Admin)**: Initiates IP blocks, revokes session keys, and enables emergency shields.
- **Support Liaison**: Coordinates disclosure communication in compliance with corporate policies.

## 4. Preconditions
- Access to the security dashboard `/api/v1/security/incidents`.
- IP block control panel access.
- Sudo access on web server hosts.

## 5. Step-by-Step Procedures
1. **Identify Threat / Leak**:
   - Security incidents are logged under `/api/v1/security/incidents` from audit log listeners and rate-limiting alerts.
   - If cross-tenant data access is logged, classify as P1 Severity.
2. **Execute Attacking IP Block**:
   - Block the malicious IP address instantly:
     ```bash
     php artisan security:block-ip <ip-address> --reason="Brute-force SQL injection attempt"
     ```
3. **Revoke Active Sessions & API Tokens**:
   - Force log out the compromised user account:
     ```bash
     php artisan auth:clear-user-tokens <user-id>
     ```
4. **Isolate Compromised Tenant**:
   - Suspend the tenant's domain:
     ```bash
     php artisan saas:suspend-tenant <tenant-id>
     ```

## 6. Failure Recovery Steps
- **IP Block Fails (Target behind Proxy)**: Manually configuration block the subnet range inside Cloudflare or Nginx config.
- **Token Revocation Fails**: Clear the entire Redis session/cache datastore.

## 7. Escalation Rules
- **P1 Incident (Cross-tenant leak / data breach)**: Notify the Legal Counsel and CTO immediately.
- **Brute Force Threat > 1 hour**: Escalate to the DevOps Hosting Provider support desk.

## 8. Verification Checklist
- [ ] Blocked IP returns `403 Forbidden` on all requests.
- [ ] User Sanctum access tokens are deleted.
- [ ] Security incident is marked as `investigating` or `mitigated`.

## 9. Rollback / Recovery Procedures
1. To unblock an accidentally throttled IP:
   ```bash
   php artisan security:unblock-ip <ip-address>
   ```
2. Restore standard tenant access:
   ```bash
   php artisan saas:unsuspend-tenant <tenant-id>
   ```

## 10. Audit Notes
- Every IP block action, token clearance, security event audit, and GPG encryption signature must be logged in the database security audit trails.
