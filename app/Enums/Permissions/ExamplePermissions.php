<?php

namespace App\Enums\Permissions;

use App\Traits\UseBaseEnum;

enum ExamplePermissions: string
{
    use UseBaseEnum;

    case VIEW_EXAMPLE = 'view:example';
}
