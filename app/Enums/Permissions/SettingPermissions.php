<?php

namespace App\Enums\Permissions;

use App\Traits\UseBaseEnum;

enum SettingPermissions: string
{
    use UseBaseEnum;

    case VIEW = 'setting:view';
    case UPDATE = 'setting:update';
}
