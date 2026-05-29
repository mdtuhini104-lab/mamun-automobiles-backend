# Mamun Automobiles ERP — Roles & Permissions Guide

This guide maps user designations and Spatie permissions to access-control matrices.

---

## 1. Enterprise Designation Permissions

| Spatie Role | Quotation Actions | Work Order Actions | QC & Handovers | Financials / Invoices |
| :--- | :--- | :--- | :--- | :--- |
| **Super Admin** | Full access | Full access | Full access | Full access |
| **Manager** | Create, Edit, Approve, Revise | View, Create, Edit | View, Edit | View, Create, Print |
| **Supervisor** | View | View, Edit (Decompose) | Manage QC checksheets | View |
| **Technician** | View | View | None | None |
| **Cashier** | None | None | None | View, Create, Print |

---

## 2. Policy-Driven Route Guards

Every endpoint enforces permission check middlewares matching Spatie permissions:

- `/api/v1/quotations/{id}/approve` -> Requires `permission:quotations.approve`
- `/api/v1/quality-control` -> Requires `permission:quality_controls.manage`
- `/api/v1/vehicle-delivery` -> Requires `permission:vehicle_deliveries.manage`
