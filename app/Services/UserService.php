<?php

namespace App\Services;

use App\Data\UserData;
use App\Interfaces\UserServiceInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\LaravelData\PaginatedDataCollection;
use Spatie\QueryBuilder\QueryBuilder;

class UserService implements UserServiceInterface
{
    public function getAllUsers(): PaginatedDataCollection
    {
        return UserData::collect(
            QueryBuilder::for(User::class)
                ->with('role')
                ->allowedSorts(['name', 'email', 'created_at'])
                ->defaultSort('-id')
                ->allowedFilters(['name', 'email'])
                ->paginate(request()->get('per_page', 10)),
            PaginatedDataCollection::class
        );
    }

    public function getUserById(int $id): User
    {
        return User::with('role')->findOrFail($id);
    }

    public function createUser(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id' => $data['role_id'],
            'phone' => $data['phone'] ?? null,
        ]);
    }

    public function updateUser(User $user, array $data): User
    {
        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'role_id' => $data['role_id'],
            'phone' => $data['phone'] ?? null,
        ]);

        if (isset($data['password']) && !empty($data['password'])) {
            $user->update([
                'password' => Hash::make($data['password']),
            ]);
        }

        return $user;
    }

    public function deleteUser(User $user): bool
    {
        return $user->delete();
    }
}
