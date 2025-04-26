<?php

namespace App\Services;

use App\Data\RoleData;
use App\Enums\BasePermissionEnums;
use App\Interfaces\RoleServiceInterface;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;
use Spatie\LaravelData\PaginatedDataCollection;
use Spatie\QueryBuilder\QueryBuilder;

class RoleService implements RoleServiceInterface
{
    public function getAllRoles(): PaginatedDataCollection
    {
        return RoleData::collect(
            QueryBuilder::for(Role::class)
                ->withCount(['permissions', 'users'])
                ->allowedSorts(['name', 'created_at'])
                ->defaultSort('-id')
                ->allowedFilters(['name'])
                ->paginate(request()->get('per_page', 10)),
            PaginatedDataCollection::class
        );
    }

    public function getGroupedPermissions(): array
    {
        return BasePermissionEnums::getGroupWithPermissions();
    }

    public function createRole(array $data): Role
    {
        $role = Role::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        $this->syncPermissions($role, $data['permissions']);

        return $role;
    }

    public function updateRole(Role $role, array $data): Role
    {
        if ($role->id === 1) {
            throw new \Exception('Admin role cannot be updated.');
        }

        $role->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        $this->syncPermissions($role, $data['permissions']);

        return $role;
    }

    public function deleteRole(Role $role): bool
    {
        if ($role->id === 1) {
            throw new \Exception('Admin role cannot be deleted.');
        }

        return $role->delete();
    }

    private function syncPermissions(Role $role, array $permissions): void
    {
        $role->permissions()->sync(
            Permission::whereIn('name', $permissions)->pluck('id')
        );
    }
}
