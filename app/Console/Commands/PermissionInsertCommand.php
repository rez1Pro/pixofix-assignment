<?php

namespace App\Console\Commands;

use App\Enums\BasePermissionEnums;
use App\Models\Permission;
use Illuminate\Console\Command;

class PermissionInsertCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:insert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insert permissions into the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $permissions = BasePermissionEnums::getAllPermissionList();

        foreach ($permissions as $permission) {
            if (!Permission::where('name', $permission)->exists()) {
                Permission::create(['name' => $permission]);
                $this->info("Created permission: {$permission}");
            } else {
                $this->line("Permission already exists: {$permission}");
            }
        }
    }
}