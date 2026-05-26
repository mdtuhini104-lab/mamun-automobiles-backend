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
        $data['password'] = Hash::make($data['password']);
        $user = $this->repository->create($data);
        
        if (isset($data['role'])) {
            $user->assignRole($data['role']);
        }
        
        return $user;
    }

    public function getUser(int $id)
    {
        return $this->repository->findById($id);
    }

    public function updateUser(int $id, array $data)
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        
        $updated = $this->repository->update($id, $data);
        
        if ($updated && isset($data['role'])) {
            $user = $this->repository->findById($id);
            $user->syncRoles([$data['role']]);
        }
        
        return $updated;
    }

    public function deleteUser(int $id)
    {
        return $this->repository->delete($id);
    }
}
