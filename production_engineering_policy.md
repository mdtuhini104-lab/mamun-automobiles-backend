# Mamun Automobiles ERP — Production Engineering Policy

This policy document governs code quality, encoding standards, deployment procedures, and error prevention guidelines for both the frontend (Vue) and backend (Laravel) repositories.

---

## 1. Frontend Stability Policies

### 1.1 Alert Dialog Elimination
* **Rule:** Never use the browser `alert()` function in production code. 
* **Reason:** Browser alert calls block the main browser thread, degrade user experience, and bypass the central styling framework.
* **Standard:** Use the central toast notification store:
  ```javascript
  import { useToastStore } from '@/stores/toast';
  const toast = useToastStore();
  toast.success('Successfully completed action.');
  toast.error('An error occurred.');
  ```

### 1.2 Centralized API Requests
* **Rule:** Never import `axios` directly in components.
* **Reason:** Importing `axios` directly bypasses the centralized interceptors that normalize authentication headers, error handling, session expiration, and validation alerts.
* **Standard:** Always import the configured `api` instance from `@/services/api.js`:
  ```javascript
  import api from '@/services/api';
  ```

### 1.3 Form Double-Submission Guard
* **Rule:** Always disable submit/save buttons while a form request is in progress.
* **Reason:** Users can double-click buttons, triggering duplicate API requests that cause redundant state mutation and double toast alerts.
* **Standard:** Bind the HTML `:disabled` attribute to the store's `saving` state:
  ```html
  <button type="submit" :disabled="store.saving">
    {{ store.saving ? 'Saving...' : 'Save' }}
  </button>
  ```

---

## 2. Backend Stability Policies

### 2.1 File Encoding Safety
* **Rule:** All PHP files must remain strictly `UTF-8` encoded.
* **Prohibition:** Never use the Windows PowerShell `>>` append operator to add comments or code to `.php` files. 
* **Reason:** PowerShell `>>` defaults to `UTF-16LE` encoding on standard systems, corrupting the file signature and throwing immediate fatal parsing errors on application boot.
* **Standard:** Edit files only via code editors or standard shell redirection that defaults to UTF-8.

### 2.2 Defensive Resource Serialization
* **Rule:** Eager-loaded relations in API Resources must check for nullability.
* **Reason:** Accessing relation properties on nullable/unassigned relationships (e.g. `$this->mechanic->id` when `assigned_mechanic_id` is null) causes fatal 500 crashes during JSON serialization.
* **Standard:** Wrap nullable relation outputs:
  ```php
  'mechanic' => $this->whenLoaded('mechanic', function () {
      return $this->mechanic ? [
          'id' => $this->mechanic->id,
          'name' => $this->mechanic->name,
      ] : null;
  }),
  ```

### 2.3 Cross-Guard Role Queries
* **Rule:** When filtering users by Spatie roles inside API controllers, use relationship subqueries (`whereHas`) rather than Spatie's `role()` query scope.
* **Reason:** Spatie's `role()` scope validates the role against the active request guard (which is `sanctum` for API calls), causing exceptions if the role was seeded under the default `web` guard.
* **Standard:**
  ```php
  $query->whereHas('roles', function ($q) use ($roleName) {
      $q->where('name', $roleName);
  });
  ```

---

## 3. Pre-Deployment Checklists

Before pushing any changes to the `main` branch, engineers must run the validation checks:

1. **Verify PHP Syntax (Recursively):**
   ```bash
   php -l <modified-file>
   ```
2. **Compile Frontend Production Assets:**
   ```bash
   npm run build
   ```
3. **Verify Routing & Boot Stability:**
   ```bash
   php artisan route:list
   ```
4. **Clear Application Cache & Autoload:**
   ```bash
   composer dump-autoload
   php artisan optimize:clear
   ```
5. **Run Centralized Safety Validator:**
   ```bash
   php mamun-automobiles-backend/scripts/validate_safety.php
   ```
