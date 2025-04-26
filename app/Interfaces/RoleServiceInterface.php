<?php

namespace App\Interfaces;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Spatie\LaravelData\PaginatedDataCollection;

interface RoleServiceInterface
{
    public function getAllRoles(): PaginatedDataCollection;

    public function getGroupedPermissions(): array;

    public function createRole(array $data): Role;

    public function updateRole(Role $role, array $data): Role;

    public function deleteRole(Role $role): bool;
}