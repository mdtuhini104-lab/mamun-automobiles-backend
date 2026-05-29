# Mamun Automobiles ERP — API Specification Guide

This guide details the endpoints, validation inputs, and idempotency guarantees exposed for stabilized CRM operations.

---

## 1. CRM Operations & Work Orders

### Live Workshop Operations Board
- **Endpoint**: `GET /api/v1/live-workshop-board`
- **Security**: Sanctum Auth, permission required: `work_orders.view`
- **Description**: Returns live aggregated data representing bay occupancy, overdue tasks, active technicians, and active work orders.
- **Sample Output**:
  ```json
  {
    "success": true,
    "bays": [],
    "active_work_orders": [],
    "technicians": [],
    "overdue_count": 0,
    "delayed_tasks": []
  }
  ```

---

## 2. Quality Control & Handover

### Submit Quality Control Checklist
- **Endpoint**: `POST /api/v1/quality-control`
- **Security**: Sanctum Auth, permission required: `quality_controls.manage`
- **Payload Headers**: `X-Idempotency-Token` (Optional, prevents duplicate click submissions)
- **Validation Payload**:
  ```json
  {
    "work_order_id": 1,
    "status": "passed",
    "checklist": {
      "engine_oil": "passed",
      "brakes": "passed",
      "visual_scratch_check": "passed"
    },
    "road_test_performed": true,
    "road_test_notes": "Engine acceleration completely smooth; brake pads biting perfectly."
  }
  ```

### Final Vehicle Delivery Handover
- **Endpoint**: `POST /api/v1/vehicle-delivery`
- **Security**: Sanctum Auth, permission required: `vehicle_deliveries.manage`
- **Validation Payload**:
  ```json
  {
    "job_card_id": 1,
    "delivered_to": "MD. Mamun Sheikh",
    "signature_path": "uploads/signatures/cust_sig_1.png",
    "delivery_photos": [
      "uploads/handover/car_left.png",
      "uploads/handover/car_right.png"
    ],
    "notes": "Handed over keys and spare tyre; customer extremely pleased with repair speed."
  }
  ```
