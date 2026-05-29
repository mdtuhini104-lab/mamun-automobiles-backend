# SOP-08: Branch Launch Procedures

## 1. Purpose
This SOP defines procedures to initialize, configure, and launch secondary workshop branches under a single tenant, ensuring strict multi-branch isolation bounds.

## 2. Scope
This SOP covers multi-branch database scope settings, branch settings, workshop bay configurations, and employee branch assignments.

## 3. Roles & Responsibilities
- **Tenant Administrator**: Initiates the branch request, sets branch parameters, and assigns mechanics.
- **Super Admin (Operations Administrator)**: Configures global routing rules and validates branch boundaries.

## 4. Preconditions
- Tenant subscription active and supporting multi-branch tiers.
- Legal licenses and physical address details validated.
- Tenant administrator credentials verified.

## 5. Step-by-Step Procedures
1. **Initialize Branch Row**:
   - Create a new branch entry under the tenant workspace:
     ```bash
     php artisan branch:create --tenant-id=<tenant-id> --name="Chittagong Branch" --code="CTG-01"
     ```
2. **Setup Workshop Bays**:
   - Provision default repair bays for the branch:
     ```bash
     php artisan branch:seed-bays --branch-id=<branch-id> --bays-count=5
     ```
3. **Assign Staff & Role Guards**:
   - Map employees and managers to the branch ID:
     ```bash
     php artisan branch:assign-employee --employee-id=<emp-id> --branch-id=<branch-id>
     ```
4. **Configure Branch Isolation Scopes**:
   - Verify `BranchMiddleware` is active to block cross-branch data leaks.

## 6. Failure Recovery Steps
- **Branch Code Collides**: Prompt the administrator to change the branch alphanumeric code to a unique value.
- **Staff Assignment Collision**: Assert that the employee does not have active assignments in another branch before completing mapping.

## 7. Escalation Rules
- **P1 Incident (Cross-branch data leakage)**: Suspend the branch scope access and escalate to the Lead Security Engineer.
- **P2 Incident (Bay provisioning error)**: Notify the Database Administrator.

## 8. Verification Checklist
- [ ] New branch is visible in `/api/v1/branches`.
- [ ] Users assigned to other branches cannot view CTG-01 job cards.
- [ ] Bay utilization analytics return correct default metrics.

## 9. Rollback / Recovery Procedures
1. To roll back a failed branch launch:
   ```bash
   php artisan branch:delete <branch-id> --force
   ```
2. Re-assert that no orphan job assignments or tasks remain mapped to the deleted branch.

## 10. Audit Notes
- Branch creation details, assigned managers, bay changes, and IP access ranges must be logged in `audit_logs`.
