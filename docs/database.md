# Mamun Automobiles ERP — Database Specification Guide

This guide details the schemas, indexes, and aggregate reporting warehouses implemented on SQLite/MySQL for high-performance enterprise operation.

---

## 1. Hardened Operational Tables

### `quality_controls`
- `id` (BigInt, PK)
- `work_order_id` (BigInt, FK to `work_orders` cascade)
- `status` (String, indexed: `passed`, `failed`)
- `supervisor_id` (BigInt, FK to `users` nullOnDelete)
- `checklist` (JSON blob containing specific QC inspection points)
- `road_test_performed` (Boolean, default false)
- `road_test_notes` (Text, nullable)
- `inspected_at` (Timestamp, nullable)

### `vehicle_deliveries`
- `id` (BigInt, PK)
- `job_card_id` (BigInt, FK to `job_cards` cascade)
- `delivered_to` (String, name of recipient)
- `signature_path` (String, file path)
- `delivery_photos` (JSON array of file paths)
- `delivered_by_id` (BigInt, FK to `users` nullOnDelete)
- `delivered_at` (Timestamp, nullable)

---

## 2. Reporting Warehouse Tables

To avoid calculating analytics directly from live transactions, the following aggregates are maintained:

1. **`quotation_conversion_reports`**: Compiles daily quotations count vs approved counts.
2. **`technician_metrics`**: Compiles estimated vs actual task durations representing mechanic labor productivity.
3. **`inventory_turnover_reports`**: Compiles daily stock turnover speeds by part ID.
4. **`bay_utilization_reports`**: Compiles total occupied minutes vs total active shift capacity by workshop bay.
5. **`customer_lifetime_reports`**: Compiles total invoice spends and frequency counts per corporate client.
