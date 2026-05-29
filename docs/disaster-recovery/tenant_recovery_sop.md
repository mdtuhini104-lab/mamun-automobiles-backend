# SOP-13: Tenant Recovery Procedures

## 1. Purpose
This SOP details procedures to restore, recover, and verify a single tenant's database rows and configuration state in Mamun Automobiles ERP after accidental data deletion or corruption.

## 2. Scope
This SOP covers the row-level data isolation bounds of a target tenant ID, database transaction records, and file uploads.

## 3. Roles & Responsibilities
- **Super Admin (Database Administrator)**: Responsible for identifying the tenant records, parsing backup files, and restoring isolated data.
- **Operations Manager**: Validates accounting and ledger reconciliation post-recovery.

## 4. Preconditions
- Target tenant ID validated and active.
- Access to the daily database GPG-encrypted backup file.
- Staging database environment available for isolated data extraction.

## 5. Step-by-Step Procedures
1. **Identify Corruption Timeframe**:
   - Determine the exact time of data deletion or corruption.
2. **Restore Full Database to Staging**:
   - Restore the matching backup archive file onto a separate staging database:
     ```bash
     php artisan db:restore --file=db_backup_TARGET_DATE.sql.gpg --env=staging
     ```
3. **Extract Tenant-Specific Data**:
   - Run the data extraction script to export target tenant rows into SQL dump file:
     ```bash
     php artisan saas:export-tenant-data --tenant-id=<tenant-id> --output=tenant_extract.sql
     ```
4. **Deploy Tenant Maintenance Shield**:
   - Suspend public access to the tenant's workspace to prevent modifications during import.
5. **Import Tenant Data to Production**:
   - Import the extracted SQL dump into the production database:
     ```bash
     php artisan saas:import-tenant-data --file=tenant_extract.sql
     ```
6. **Unsuspend Tenant Workspace**:
   - Enable tenant access and clear configuration cache holds.

## 6. Failure Recovery Steps
- **Data Import Constraint Errors**: Disable database foreign keys constraints temporarily during import, run migration checks, and re-enable.
- **Extraction Script Errors**: Extract row records table-by-table using explicit `WHERE tenant_id = ?` queries.

## 7. Escalation Rules
- **P1 Incident (Restored data contains rows from other tenants)**: Immediately rollback the import, keep the tenant suspended, and escalate to the Lead Architect.
- **Tenant Recovery > 4 hours**: Inform the customer representative.

## 8. Verification Checklist
- [ ] Tenant database row counts match staging extract logs.
- [ ] Cross-tenant boundaries are strictly isolated (zero data leaks).
- [ ] Customer transaction ledgers are complete.

## 9. Rollback / Recovery Procedures
1. If import causes production instability, run tenant purge:
   ```bash
   php artisan saas:purge-tenant <tenant-id> --force
   ```
2. Re-import the backup data using individual transaction batches.

## 10. Audit Notes
- Every tenant data extraction, import size, SQL queries run, operator ID, and recovery reason must be logged in the database audit log table.
