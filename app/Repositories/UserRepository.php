<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{

    public function getAll()
    {
        return User::with('role')->get();
    }

    public function findById(int $id): User
    {
        return User::with('role')->findOrFail($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::with('role')->where('email', $email)->first();
    }


    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(int $id, array $data): User
    {
        $user = $this->findById($id);
        $user->update($data);
        
        return $user;
    }

    public function delete(int $id): bool
    {
        $user = $this->findById($id);
        return $user->delete();
    }
}