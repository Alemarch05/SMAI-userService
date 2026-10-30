<?php

namespace App\Services;

use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;

class UserServices
{
    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getAllUsers()
    {
        return $this->userRepository->getAll();
    }

    public function getUserById(int $id)
    {
        return $this->userRepository->findById($id);
    }

    public function getUserByEmail(string $email)
    {
        return $this->userRepository->findByEmail($email);
    }

    public function createUser(array $data)
    {
    
        $data['password'] = Hash::make($data['password']);
        
        return $this->userRepository->create($data);
    }

    public function updateUser(int $id, array $data)
    {
        
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            
            unset($data['password']);
        }

        return $this->userRepository->update($id, $data);
    }

    public function deleteUser(int $id)
    {
        return $this->userRepository->delete($id);
    }
}