<?php

namespace App\Interfaces;

use App\Models\User;
use Spatie\LaravelData\PaginatedDataCollection;

interface UserServiceInterface
{
    public function getAllUsers(): PaginatedDataCollection;

    public function getUserById(int $id): User;

    public function createUser(array $data): User;

    public function updateUser(User $user, array $data): User;

    public function deleteUser(User $user): bool;
}