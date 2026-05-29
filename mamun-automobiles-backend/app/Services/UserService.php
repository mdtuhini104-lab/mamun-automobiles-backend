<?php

namespace App\Services;

use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;

class UserService extends BaseService
{
    protected $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function listUsers(array $filters = [])
    {
        return $this->repository->getAll($filters);
    }

    public function createUser(array $data)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
            $data['password'] = Hash::make($data['password']);
            
            // Extract workforce/employee specific data
            $employeeData = [
                'department_id' => $data['department_id'] ?? null,
                'designation_id' => $data['designation_id'] ?? null,
                'employee_code' => $data['employee_code'] ?? null,
                'first_name' => $data['first_name'] ?? $data['name'],
                'last_name' => $data['last_name'] ?? '',
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'nid' => $data['nid'] ?? null,
                'salary' => $data['salary'] ?? 0.0,
                'joining_date' => $data['joining_date'] ?? now()->format('Y-m-d'),
                'status' => $data['status'] ?? \App\Constants\WorkforceConstants::STATUS_ACTIVE,
                'availability_status' => $data['availability_status'] ?? \App\Constants\WorkforceConstants::AVAIL_AVAILABLE,
            ];

            // If employee_code is not provided, generate one dynamically
            if (empty($employeeData['employee_code'])) {
                $nextId = (\App\Models\Employee::max('id') ?? 0) + 1;
                $employeeData['employee_code'] = 'EMP-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
            }

            // Create User
            $user = $this->repository->create($data);
            
            if (isset($data['role'])) {
                $user->assignRole($data['role']);
            }
            
            // Sync legacy columns on user
            $user->update([
                'department_id' => $data['department_id'] ?? null,
                'designation_id' => $data['designation_id'] ?? null,
                'shift_id' => $data['shift_id'] ?? null,
            ]);

            // Create Employee profile
            $employeeData['user_id'] = $user->id;
            $employee = \App\Models\Employee::create($employeeData);

            // Sync skills if provided
            if (isset($data['skills'])) {
                $employee->skills()->sync($data['skills']);
            }

            return $user;
        });
    }

    public function getUser(int $id)
    {
        return $this->repository->findById($id);
    }

    public function updateUser(int $id, array $data)
    {
        if (array_key_exists('password', $data)) {
            if ($data['password'] === null || $data['password'] === '') {
                unset($data['password']);
            } else {
                $data['password'] = Hash::make($data['password']);
            }
        }
        
        return \Illuminate\Support\Facades\DB::transaction(function () use ($id, $data) {
            $updated = $this->repository->update($id, $data);
            
            $user = $this->repository->findById($id);
            if (!$user) {
                return false;
            }

            if ($updated && isset($data['role'])) {
                $user->syncRoles([$data['role']]);
            }

            // Update legacy columns on user
            $user->update([
                'department_id' => $data['department_id'] ?? null,
                'designation_id' => $data['designation_id'] ?? null,
                'shift_id' => $data['shift_id'] ?? null,
            ]);

            // Update or create Employee profile
            $employeeData = [
                'department_id' => $data['department_id'] ?? null,
                'designation_id' => $data['designation_id'] ?? null,
                'employee_code' => $data['employee_code'] ?? null,
                'first_name' => $data['first_name'] ?? $data['name'] ?? $user->name,
                'last_name' => $data['last_name'] ?? '',
                'phone' => $data['phone'] ?? $user->phone,
                'address' => $data['address'] ?? $user->address,
                'nid' => $data['nid'] ?? $user->nid,
                'salary' => $data['salary'] ?? $user->salary,
                'joining_date' => $data['joining_date'] ?? $user->joining_date,
                'status' => $data['status'] ?? null,
                'availability_status' => $data['availability_status'] ?? null,
            ];

            // Remove null entries to avoid overwriting with nulls if they were not provided in simple form
            $employeeData = array_filter($employeeData, fn($val) => $val !== null);

            $employee = \App\Models\Employee::updateOrCreate(
                ['user_id' => $user->id],
                $employeeData
            );

            // Sync skills if provided
            if (isset($data['skills'])) {
                $employee->skills()->sync($data['skills']);
            }

            return $updated;
        });
    }

    public function deleteUser(int $id)
    {
        return $this->repository->delete($id);
    }
}
