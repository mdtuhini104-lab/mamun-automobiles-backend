# SOP-05: Websocket Failure Procedures

## 1. Purpose
This SOP provides troubleshooting guidelines and mitigation procedures for websocket disconnect events, connection drops, and degraded realtime states in Mamun Automobiles ERP.

## 2. Scope
This SOP covers the frontend Echo client status notifications, Pusher channels, Laravel Websockets server, and operator realtime dashboards.

## 3. Roles & Responsibilities
- **Frontend Engineer**: Responsible for client-side Echo reconnection loops, connection status displays, and degraded fallback states.
- **DevOps Engineer**: Monitors Laravel Websocket server memory, sockets capacity, and port allocations.

## 4. Preconditions
- Access to the frontend console diagnostics panel.
- Sudo access to the Websockets server VPS.
- Firewall configurations access (port 6001 or Pusher configuration).

## 5. Step-by-Step Procedures
1. **Identify Realtime State Degrade**:
   - Operator dashboard displays status states: `Connected`, `Reconnecting`, `Disconnected`, or `Degraded`.
   - If the Echo status returns `Degraded` or `Reconnecting` for more than 15 seconds, proceed with the diagnostics sequence.
2. **Execute Connection Health Diagnostics**:
   - Check if the websockets process is running on the host server:
     ```bash
     sudo systemctl status laravel-websockets
     ```
   - Verify that port 6001 is open and listening:
     ```bash
     netstat -tpln | grep 6001
     ```
3. **Simulate Connection Refresh**:
   - Run a websocket connection refresh event via the debug stress endpoints (on staging/testing environment only):
     ```bash
     curl -X POST -H "Accept: application/json" -d "action=simulate_disconnect" http://localhost/api/v1/system/stress/websocket-disconnect
     ```
4. **Restart Websockets Server**:
   - If the websockets service has stalled or crashed, run:
     ```bash
     sudo systemctl restart laravel-websockets
     ```

## 6. Failure Recovery Steps
- **Client Failing to Reconnect**: Force reload client browser tab to clear the local storage Echo connection cache.
- **Port Conflict**: Change the default websockets port inside `config/websockets.php` and restart the service.

## 7. Escalation Rules
- **P2 Incident (Websockets server offline > 15 mins)**: Escalate to the DevOps Engineer.
- **P3 Incident (Latency/delay on realtime updates)**: Alert the Frontend Development team.

## 8. Verification Checklist
- [ ] Operator dashboard shows green `Connected` state indicator.
- [ ] Browser developer console contains zero websocket connection error logs.
- [ ] Realtime dashboard receives event broad-casts successfully.

## 9. Rollback / Recovery Procedures
1. If the websocket service fails after a configuration deployment, immediately revert the `.env` websockets configuration values to the last stable state.
2. Restart the websockets service:
   ```bash
   sudo systemctl restart laravel-websockets
   ```

## 10. Audit Notes
- All websocket disconnection events, server restarts, and port allocation changes must be recorded in the infrastructure deployment logs.
