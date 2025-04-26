<?php

namespace App\Enums\Permissions;

use App\Traits\UseBaseEnum;

enum RolePermissions: string
{
    use UseBaseEnum;

    case VIEW_ROLE = 'role:view';
    case CREATE_ROLE = 'role:create';
    case UPDATE_ROLE = 'role:update';
    case DELETE_ROLE = 'role:delete';
}
