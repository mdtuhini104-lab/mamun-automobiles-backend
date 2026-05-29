# SOP-07: Customer Onboarding Procedures

## 1. Purpose
This SOP standardizes the step-by-step procedures to register, configure, and initialize new workshop tenants on the Mamun Automobiles ERP platform.

## 2. Scope
This SOP applies to new SaaS customer registrations, domain settings, initial database seeds, and default branch configurations.

## 3. Roles & Responsibilities
- **Onboarding Specialist (Customer Success)**: Directs the tenant through registration, handles data imports, and sets initial preferences.
- **Super Admin (Operations Administrator)**: Approves custom domain mappings and validates multi-tenant database isolation bounds.

## 4. Preconditions
- Tenant signup request submitted successfully.
- Valid corporate email and domain credentials.
- Workspace initialization permissions configured.

## 5. Step-by-Step Procedures
1. **Tenant Provisioning**:
   - Register the new tenant account via the administrative dashboard or CLI console:
     ```bash
     php artisan saas:create-tenant --name="Workshop Name" --domain="workshop.mamunerp.com"
     ```
2. **Database Schema Seeding**:
   - Execute database seeders for the newly generated tenant:
     ```bash
     php artisan saas:seed-tenant-defaults --tenant-id=<tenant-id>
     ```
3. **Master Data CSV Imports**:
   - Import default inventory parts and customer contacts via the onboarding APIs:
     ```bash
     php artisan onboarding:import-master-data --tenant-id=<tenant-id> --parts-csv=parts.csv --customers-csv=customers.csv
     ```
4. **MFA Grace Period Initialization**:
   - Configure MFA setup warnings. Initialize the 3-day grace period parameters for tenant administrators.
5. **Setup Tenant Health Monitor**:
   - Register the tenant ID in the daily telemetry analytics monitor.

## 6. Failure Recovery Steps
- **Tenant Domain Collides**: Re-run the tenant creation command using an incremental suffix on the subdomain name.
- **Master Data Import Fails**: Truncate the partial tables and re-run imports with sanitized UTF-8 character encoding on the CSV files.

## 7. Escalation Rules
- **P1 Incident (Cross-tenant data exposure on signup)**: Suspend the onboarding system immediately and escalate to the Lead Security Engineer.
- **P2 Incident (Database seeding failure > 15 mins)**: Contact the DBA support team.

## 8. Verification Checklist
- [ ] New tenant row exists in `tenants` database table.
- [ ] Row-level tenant isolation enforces active scopes successfully.
- [ ] MFA grace banner shows on the tenant's login screen.
- [ ] Default branch configurations match default settings.

## 9. Rollback / Recovery Procedures
1. If onboarding fails midway, clean up the database to prevent partial state corruption:
   ```bash
   php artisan saas:purge-tenant <tenant-id> --force
   ```
2. Clear tenant-related caches:
   ```bash
   php artisan cache:forget tenant_settings_<tenant-id>
   ```

## 10. Audit Notes
- Every tenant signup, import hash count, database provisioning time, and GPG-signed approval metadata must be logged in `audit_logs`.
