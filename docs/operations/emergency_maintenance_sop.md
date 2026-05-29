# SOP-11: Emergency Maintenance Procedures

## 1. Purpose
This SOP establishes quick-response procedures to enable maintenance modes, isolate tenants, and secure the ERP during active production emergencies.

## 2. Scope
This SOP applies to core application servers, database instances, frontend portals, and mobile API routing middleware.

## 3. Roles & Responsibilities
- **Super Admin (Operations Administrator)**: Authorized to enable/disable maintenance mode and toggle database connections.
- **Support Manager**: Handles communication templates to notify active workshop tenants of emergency windows.

## 4. Preconditions
- Active incident severity classified as P1 or P2.
- Administrative console access.
- Firewall security manager credentials.

## 5. Step-by-Step Procedures
1. **Initiate Maintenance Mode**:
   - Put the system into maintenance mode immediately to block public requests while allowing administrative IP bypass:
     ```bash
     php artisan down --secret="emergency-bypass-token-123"
     ```
2. **Deploy Emergency Maintenance Middleware**:
   - Assert that `EmergencyMaintenanceMiddleware` is active and returns `503 Service Unavailable` for standard routes.
3. **Isolate Affected Tenant (if tenant-specific incident)**:
   - Suspend access for the affected tenant to secure other clients:
     ```bash
     php artisan saas:suspend-tenant <tenant-id>
     ```
4. **Inspect Application Logs**:
   - Access the emergency logs to identify the root cause:
     ```bash
     tail -n 100 storage/logs/laravel.log
     ```

## 6. Failure Recovery Steps
- **Maintenance Mode Fails to Engage**: Manually drop incoming port 80/443 traffic in the Nginx or Cloudflare configuration.
- **Bypass Token Exposure**: Purge the cache and change the secret token parameter immediately.

## 7. Escalation Rules
- **P1 Incident (Database corruption / security leak)**: Escalate to the CEO, CTO, and DBA Lead.
- **Maintenance Window > 30 minutes**: Dispatch status emails to all tenant admins.

## 8. Verification Checklist
- [ ] Endpoint `/api/v1/health` returns status code `503` for non-whitelisted IPs.
- [ ] Active background database connections drop to safe levels.
- [ ] Suspended tenant subdomains return `403 Forbidden`.

## 9. Rollback / Recovery Procedures
1. Disable maintenance mode:
   ```bash
   php artisan up
   ```
2. Clear routes and configs cache:
   ```bash
   php artisan optimize:clear
   ```

## 10. Audit Notes
- Every emergency maintenance execution, bypass token usage, duration, operator signature, and postmortem link must be documented in `audit_logs`.
