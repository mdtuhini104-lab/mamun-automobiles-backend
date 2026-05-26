<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\PermissionAuditLog;
use Illuminate\Support\Facades\DB;

class RolePermissionController extends Controller
{
    private function logAudit($action, $description, $target, $payload = null)
    {
        PermissionAuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'target_type' => get_class($target),
            'target_id' => $target->id,
            'payload' => $payload,
            'ip_address' => request()->ip(),
        ]);
    }

    public function getPermissions()
    {
        $permissions = Permission::all()->groupBy('module');
        return response()->json(['success' => true, 'data' => $permissions]);
    }

    public function getRoles()
    {
        $roles = Role::with('permissions')->get();
        return response()->json(['success' => true, 'data' => $roles]);
    }

    public function createRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'array'
        ]);

        DB::beginTransaction();
        try {
            $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);
            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            }
            $this->logAudit('role_created', "Created role {$role->name}", $role, ['permissions' => $request->permissions]);
            DB::commit();
            
            // clear cache
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            return response()->json(['success' => true, 'data' => $role->load('permissions')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateRole(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        
        if ($role->name === 'Super Admin') {
            return response()->json(['success' => false, 'message' => 'Cannot modify Super Admin role directly.'], 403);
        }

        $request->validate([
            'name' => 'required|string|unique:roles,name,'.$id,
            'permissions' => 'array'
        ]);

        DB::beginTransaction();
        try {
            $role->name = $request->name;
            $role->save();
            
            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            }
            $this->logAudit('role_updated', "Updated role {$role->name}", $role, ['permissions' => $request->permissions]);
            DB::commit();

            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            
            return response()->json(['success' => true, 'data' => $role->load('permissions')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteRole($id)
    {
        $role = Role::findOrFail($id);
        if ($role->name === 'Super Admin') {
            return response()->json(['success' => false, 'message' => 'Cannot delete Super Admin role.'], 403);
        }

        DB::beginTransaction();
        try {
            $this->logAudit('role_deleted', "Deleted role {$role->name}", $role);
            $role->delete();
            DB::commit();

            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            return response()->json(['success' => true, 'message' => 'Role deleted successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function cloneRole(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $request->validate(['new_name' => 'required|string|unique:roles,name']);

        DB::beginTransaction();
        try {
            $newRole = Role::create(['name' => $request->new_name, 'guard_name' => 'web']);
            $newRole->syncPermissions($role->permissions);
            
            $this->logAudit('role_cloned', "Cloned role {$role->name} to {$newRole->name}", $newRole, ['original_role_id' => $role->id]);
            DB::commit();

            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            return response()->json(['success' => true, 'data' => $newRole->load('permissions')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function assignUserPermissions(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $request->validate([
            'roles' => 'array',
            'permissions' => 'array'
        ]);

        DB::beginTransaction();
        try {
            if ($request->has('roles')) {
                $user->syncRoles($request->roles);
            }
            if ($request->has('permissions')) {
                $user->syncPermissions($request->permissions);
            }
            
            $this->logAudit('user_permissions_updated', "Updated permissions for user {$user->name}", $user, [
                'roles' => $request->roles,
                'permissions' => $request->permissions
            ]);
            DB::commit();

            return response()->json(['success' => true, 'message' => 'User permissions updated successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getAuditLogs()
    {
        $logs = PermissionAuditLog::with('user:id,name')->latest()->take(100)->get();
        return response()->json(['success' => true, 'data' => $logs]);
    }
}
