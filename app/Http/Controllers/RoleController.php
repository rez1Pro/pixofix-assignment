<?php

namespace App\Http\Controllers;

use App\Data\RoleData;
use App\Enums\BasePermissionEnums;
use App\Interfaces\RoleServiceInterface;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Routing\Controllers\HasMiddleware;
use App\Enums\Permissions\RolePermissions;

class RoleController extends Controller implements HasMiddleware
{
    protected RoleServiceInterface $roleService;

    public function __construct(RoleServiceInterface $roleService)
    {
        $this->roleService = $roleService;
    }

    public static function middleware(): array
    {
        return [
            checkPermission(RolePermissions::VIEW_ROLE, only: ['index']),
            checkPermission(RolePermissions::CREATE_ROLE, only: ['create', 'store']),
            checkPermission(RolePermissions::UPDATE_ROLE, only: ['edit', 'update']),
            checkPermission(RolePermissions::DELETE_ROLE, only: ['destroy']),
        ];
    }

    public function index()
    {
        return Inertia::render('Roles/Index', [
            'roles' => $this->roleService->getAllRoles()
        ]);
    }

    public function create()
    {
        return Inertia::render('Roles/Create', [
            'permissionGroups' => $this->roleService->getGroupedPermissions()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'description' => 'nullable|string',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        try {
            $this->roleService->createRole($validated);
            return redirect()->route('users.roles.index')
                ->with('success', 'Role created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function edit(Role $role)
    {
        return Inertia::render('Roles/Edit', [
            'role' => RoleData::from($role->load('permissions')),
            'existingPermissions' => $this->roleService->getGroupedPermissions()
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'nullable|string',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name'
        ], [
            'permissions.required' => 'Please select at least one permission.',
            'permissions.*.exists' => 'The :attribute selected is invalid.'
        ]);

        try {
            $this->roleService->updateRole($role, $validated);
            return redirect()->back()->with('success', 'Role updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroy(Role $role)
    {
        try {
            $this->roleService->deleteRole($role);
            return redirect()->route('users.roles.index')
                ->with('success', 'Role deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('users.roles.index')
                ->with('error', $e->getMessage());
        }
    }
}
