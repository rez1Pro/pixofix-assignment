<?php

namespace App\Enums;

use App\Traits\UseBaseEnum;

enum RoleEnums: string
{
    use UseBaseEnum;

    case ADMIN = 'Admin';
    case USER = 'User';
}
