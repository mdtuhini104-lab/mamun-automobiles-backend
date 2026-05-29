# Mamun Automobiles ERP — Production Deployment Guide

This guide governs the environment, database configurations, and compiler schedulers required to deploy changes safely.

---

## 1. Database & Migrations

When running migrations in staging or production:
1. Enforce a backup before starting:
   ```bash
   php artisan db:backup
   ```
2. Run database schema adjustments safely:
   ```bash
   php artisan migrate --force
   ```
3. Run verification test suite and route listing:
   ```bash
   php artisan route:list
   ```

---

## 2. Cron Jobs & Warehouse Aggregations

To maintain aggregations in the Reporting Warehouse, schedule the following cron compilation on the production VPS server:

- **Warehouse Aggregation Compiler** (Compiles nightly at 00:05):
  ```cron
  5 0 * * * cd /var/www/mamun-automobiles && php artisan Mamun:CompileWarehouseAggregates >> storage/logs/warehouse.log 2>&1
  ```

---

## 3. Deployment Cleanups

Whenever code is updated:
1. Clear cache holds and optimize bootstrap paths:
   ```bash
   php artisan optimize:clear
   php artisan config:cache
   php artisan route:cache
   ```
2. Verify asset bundles compile cleanly:
   ```bash
   npm run build
   ```
