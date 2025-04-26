<?php

namespace App\Enums\Permissions;

use App\Traits\UseBaseEnum;

enum UserPermissions: string
{
    use UseBaseEnum;

    case VIEW_USER = 'user:view';
    case CREATE_USER = 'user:create';
    case UPDATE_USER = 'user:update';
    case DELETE_USER = 'user:delete';
}
