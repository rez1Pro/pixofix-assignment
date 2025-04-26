<?php

namespace App\Enums\Permissions;

use App\Traits\UseBaseEnum;

enum OrderManagementPermissions: string
{
    use UseBaseEnum;

    case VIEW_ORDERS = 'view:orders';
    case CREATE_ORDERS = 'create:orders';
    case EDIT_ORDERS = 'edit:orders';
    case DELETE_ORDERS = 'delete:orders';

    case VIEW_FILES = 'view:files';
    case CREATE_FILES = 'create:files';
    case EDIT_FILES = 'edit:files';
    case DELETE_FILES = 'delete:files';

    case VIEW_CLAIMS = 'view:claims';

}
