# SOP-02: Backup & Restore Procedures

## 1. Purpose
This SOP governs the automatic creation, encryption, rotation, and restoration of the database and files for Mamun Automobiles ERP, ensuring zero data loss and business continuity.

## 2. Scope
This SOP covers the multi-tenant database instances, system logs, environment profiles, and user attachments stored in local storage or S3 repositories.

## 3. Roles & Responsibilities
- **Super Admin (Database Administrator)**: Responsible for backup scheduling, encryption key rotation, and running database restorations.
- **Operations Manager**: Audits weekly recovery drill outcomes.

## 4. Preconditions
- Access to the GPG encryption keys.
- Write permissions to the local backup volume `/var/backups/mamunerp` or S3 bucket credentials.
- Root or sudo privileges on the SQL server.

## 5. Step-by-Step Procedures
1. **Automated Backup Scheduling**:
   - The backup script runs daily at 02:00 AM via cron schedule.
   - Run backup manually using:
     ```bash
     php artisan db:backup
     ```
2. **Encrypted Archive Verification**:
   - The backup engine creates an archive and encrypts it using AES-256 via GPG.
   - Locate the encrypted file: `storage/backups/db_backup_YYYY_MM_DD.sql.gpg`.
3. **Weekly Restoration Drills**:
   - Once a week, restore the backup archive to a staging/testing database to verify its integrity:
     ```bash
     php artisan db:restore --file=db_backup_YYYY_MM_DD.sql.gpg --env=staging
     ```
4. **Backup Rotation Rules**:
   - Daily backups are retained for 7 days.
   - Weekly backups are retained for 30 days.
   - Monthly backups are retained for 365 days.
   - Old archives are automatically rotated and purged via the cleanup script.

## 6. Failure Recovery Steps
- **GPG Encryption Fails**: Terminate the backup job, delete the unencrypted temporary archive immediately, and alert the Administrator of key signature mismatches.
- **S3 Upload Stalls**: Fallback to local disk storage and queue the archive for retry upload in 10 minutes.

## 7. Escalation Rules
- **P1 Severity Incident (Backup Failure > 24 hours)**: Escalate to the Lead System Architect immediately.
- **P2 Severity Incident (Restoration verification failure on staging)**: Inform the DBA team.

## 8. Verification Checklist
- [ ] GPG signature verification returns `OK`.
- [ ] Database record counts match between primary and restored database.
- [ ] Row-level tenant isolation works correctly on the restored instance.
- [ ] Decryption keys pass integrity check validations.

## 9. Rollback / Recovery Procedures
1. In the event of a corrupt restore, immediately rollback to the previous day's snapshot:
   ```bash
   php artisan db:restore --file=db_backup_PREVIOUS_DAY.sql.gpg --force
   ```
2. Re-index database foreign constraints:
   ```bash
   php artisan db:reindex
   ```

## 10. Audit Notes
- All backup actions, backup sizes, GPG validation fingerprints, and weekly restoration drill logs are recorded in the system audit logs database table.
