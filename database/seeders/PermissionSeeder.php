<?php

namespace Database\Seeders;

use App\Enums\BasePermissionEnums;
use App\Enums\Enums\Permissions\UserPermissionEnums;
use App\Enums\RoleEnums;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (RoleEnums::getValues() as $role) {
            Role::create([
                'name' => $role,
                'description' => "This is a {$role} role"
            ]);
        }

        foreach (BasePermissionEnums::getAllPermissionList() as $permission) {
            Permission::create([
                'name' => $permission
            ]);
        }

        $admin = Role::where('name', RoleEnums::ADMIN)->first();
        $admin->permissions()->sync(Permission::all());
    }
}
