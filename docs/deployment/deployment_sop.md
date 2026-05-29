# SOP-01: Production Deployment Procedures

## 1. Purpose
This Standard Operating Procedure (SOP) defines the mandatory steps, checks, and safety rules required to deploy software updates to the Mamun Automobiles ERP production environments with zero downtime and zero data loss.

## 2. Scope
This SOP applies to all backend code updates, database migrations, configuration changes, and frontend asset builds deployed on staging, production servers, and VPS hosts.

## 3. Roles & Responsibilities
- **Super Admin (Lead Engineer)**: Approves deployment, runs commands, triggers migrations, and initiates rollback.
- **Operations Manager**: Observes system metrics post-deploy and informs stakeholders of maintenance windows.
- **QA Engineer**: Executes post-deploy smoke tests.

## 4. Preconditions
- All automated unit/feature tests must pass successfully (`php artisan test`).
- A recent database backup must exist and be validated.
- The environment configuration file (`.env`) is locked and verified.
- The deployment branch (`main`) is clean, built, and has passed the CI pipeline.

## 5. Step-by-Step Procedures
1. **Pre-deployment Verification**:
   - Check system disk space and CPU utilization:
     ```bash
     df -h
     top -n 1
     ```
2. **Draining Queues**:
   - Pause queue processing before starting migrations to prevent locking:
     ```bash
     php artisan horizon:pause
     ```
3. **Execute Pre-Deploy Backup**:
   - Trigger a backup of the current database:
     ```bash
     php artisan db:backup
     ```
4. **Pull Latest Code and Compile Assets**:
   - Fetch main branch updates and install composer dependencies:
     ```bash
     git pull origin main
     composer install --no-dev --optimize-autoloader
     ```
   - Compile frontend assets:
     ```bash
     npm install
     npm run build
     ```
5. **Run Database Migrations**:
   - Run Laravel migrations using the `--force` flag:
     ```bash
     php artisan migrate --force
     ```
6. **Flush and Rebuild Cache**:
   - Rebuild performance bootstrap caches:
     ```bash
     php artisan optimize:clear
     ```
7. **Restart Queues and Services**:
   - Restart the Horizon queue runner and reload php-fpm/Nginx:
     ```bash
     php artisan horizon:terminate
     ```

## 6. Failure Recovery Steps
- **Migration Fails**: Immediately abort deployment, block additional traffic using emergency maintenance mode, and inspect the migration log file.
- **Vite Compile Fails**: Revert to the last compiled assets in the `dist` directory.
- **Queue/Horizon Stalls**: Clear the Redis queue cache and restart Supervisor:
  ```bash
  sudo systemctl restart supervisor
  ```

## 7. Escalation Rules
- **P1 Severity Incident (Outage > 5 mins)**: Notify the Lead Architect and CEO immediately.
- **P2 Severity Incident (Queue/Migration Lock > 15 mins)**: Notify the Operations Manager and DevOps team.

## 8. Verification Checklist
- [ ] Database migrations successfully completed in schema logs.
- [ ] Horizon dashboard shows active status and zero failed jobs.
- [ ] Echo websocket connection returns `Connected` status code.
- [ ] Multi-tenant isolation remains intact (verified via test requests).
- [ ] Homepage load time is within acceptable limits (< 1s).

## 9. Rollback / Recovery Procedures
1. Enable maintenance mode:
   ```bash
   php artisan down
   ```
2. Rollback the database migration:
   ```bash
   php artisan migrate:rollback --force
   ```
3. Restore database snapshot if rollback fails:
   ```bash
   php artisan db:restore <backup-file>
   ```
4. Revert code to the last stable git commit hash:
   ```bash
   git checkout <stable-commit-hash>
   composer install --no-dev --optimize-autoloader
   php artisan optimize:clear
   ```
5. Disable maintenance mode:
   ```bash
   php artisan up
   ```

## 10. Audit Notes
- Every deployment must be logged in `CHANGELOG.md` with:
  - Version number.
  - Operator email.
  - Deprecated files and tables.
  - Commits range.
  - Verification signature.
