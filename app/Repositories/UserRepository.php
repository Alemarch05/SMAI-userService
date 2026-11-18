<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserRepository
{
    /**
     * @return Collection<int, User>
     */
    public function getAll(): Collection
    {
        return User::with('role')->get();
    }

    public function findById(int $id): ?User
    {
        return User::with('role')->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::with('role')->where('email', $email)->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): User
    {
        return User::create($data)->load('role');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(int $id, array $data): ?User
    {
        $user = $this->findById($id);

        if (! $user) {
            return null;
        }

        $user->update($data);

        return $user->fresh('role');
    }

    public function delete(int $id): bool
    {
        $user = $this->findById($id);

        if (! $user) {
            return false;
        }

        return (bool) $user->delete();
    }
}
