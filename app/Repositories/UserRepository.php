<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{

    public function findByEmail(string $email): ?User
    {
        return User::with('role')->where('email', $email)->first();
    }


    public function create(array $data): User
    {
        return User::create($data);
    }
}