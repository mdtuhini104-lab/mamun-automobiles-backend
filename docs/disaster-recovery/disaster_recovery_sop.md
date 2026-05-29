# SOP-14: Disaster Recovery Procedures

## 1. Purpose
This SOP outlines the end-to-end procedures to recover the entire Mamun Automobiles ERP system in the event of a catastrophic server hardware failure, hosting provider outage, or complete data loss.

## 2. Scope
This SOP covers the entire multi-tenant infrastructure, server rebuilds, GPG database decryption, DNS routing switchovers, and service health validations.

## 3. Roles & Responsibilities
- **Infrastructure Lead (Super Admin)**: Manages VPS provisioning, system re-installation, configuration loads, and restores.
- **CTO**: Decides when to initiate the disaster recovery procedures and manages external stakeholders communication.
- **DevOps Team**: Executes DNS redirects and re-configures SSL certificates.

## 4. Preconditions
- Access to the GPG recovery key stored in the secure offline vaults.
- Account access to the secondary/failover hosting provider.
- Permission to change public DNS records.

## 5. Step-by-Step Procedures
1. **Declare Disaster State**:
   - The CTO declares the disaster state if the primary system has been offline for more than 15 minutes with no recovery timeframe.
2. **Provision Failover Server**:
   - Spin up a new VPS node using the standard production configuration template.
3. **Execute Bootstrap Script**:
   - Run the VPS bootstrap script to install PHP, Nginx, Redis, Supervisor, and database binaries:
     ```bash
     curl -s https://raw.githubusercontent.com/mamun-automobiles/backend/main/vps_bootstrap.sh | bash
     ```
4. **Pull Source Code and Configuration**:
   - Clone the source repository and copy the environment parameters from the secure configuration backup.
5. **Decrypt and Restore Database**:
   - Fetch the latest daily database backup archive from the secure offsite repository.
   - Decrypt the archive using the GPG recovery key:
     ```bash
     gpg --decrypt db_backup_LATEST.sql.gpg > db_backup.sql
     ```
   - Load the decrypted SQL dump into the active database server:
     ```bash
     mysql -u root -p mamunerp < db_backup.sql
     ```
6. **Deploy Application Migrations**:
   - Run database migrations to ensure the schema is up-to-date:
     ```bash
     php artisan migrate --force
     ```
7. **Switch DNS Routing**:
   - Change the primary domain routing IP (A record) to point to the failover server IP address.
8. **Rebuild Cache and Restart Services**:
   - Clear caches and start Nginx, Horizon, and Laravel Websockets.

## 6. Failure Recovery Steps
- **DNS Propagation Delay**: Inform clients of temporary access paths (e.g. failover.mamunerp.com) during DNS caching.
- **Failover Server Provisioning Error**: Instantly switch to a secondary failover provider region.

## 7. Escalation Rules
- **DNS propagation failure > 1 hour**: Escalate to the Domain Registrar network support team.
- **P1 Severity Incident (Disaster Recovery execution > 2 hours)**: Initiate manual backup business processes for workshops.

## 8. Verification Checklist
- [ ] Central platform and tenant subdomains load successfully.
- [ ] Database health check `/api/v1/health` returns `connected`.
- [ ] Multi-tenant isolation limits remain strictly enforced.
- [ ] Realtime Horizon and websocket services are fully operational.

## 9. Rollback / Recovery Procedures
1. Once the primary datacenter is restored and stable, sync changes from the failover server back to the primary node.
2. Re-point DNS A records back to the primary server IP address.
3. Terminate the failover VPS node.

## 10. Audit Notes
- Every disaster recovery declaration, backup extraction, GPG decryption, DNS change, recovery duration, and final status check must be logged in the operations changelog repository.
