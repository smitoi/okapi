<?php

namespace App\Models;

class Role extends \Spatie\Permission\Models\Role
{
    public const PUBLIC_ROLE = 'Public';
    public const ADMIN_ROLE = 'Admin';

    public const PROTECTED_ROLES = [
        self::PUBLIC_ROLE,
        self::ADMIN_ROLE,
    ];
}
