<?php

declare(strict_types=1);

namespace App\Support;

final class Permissions
{
    public const USERS_VIEW = 'users.view';

    public const USERS_CREATE = 'users.create';

    public const USERS_EDIT = 'users.edit';

    public const USERS_DEACTIVATE = 'users.deactivate';

    public const USERS_ASSIGN_ROLES = 'users.assign_roles';

    public const CAMPUSES_VIEW = 'campuses.view';

    public const CAMPUSES_MANAGE = 'campuses.manage';

    public const ZONES_VIEW = 'zones.view';

    public const ZONES_MANAGE = 'zones.manage';

    public const BUILDINGS_VIEW = 'buildings.view';

    public const BUILDINGS_MANAGE = 'buildings.manage';

    public const ROOMS_VIEW = 'rooms.view';

    public const ROOMS_MANAGE = 'rooms.manage';

    public const AUDIT_VIEW = 'audit.view';

    /** @return array<string, string> */
    public static function definitions(): array
    {
        return [
            self::USERS_VIEW => 'View user accounts',
            self::USERS_CREATE => 'Create user accounts',
            self::USERS_EDIT => 'Edit user profiles',
            self::USERS_DEACTIVATE => 'Activate or deactivate user accounts',
            self::USERS_ASSIGN_ROLES => 'Assign and revoke roles',
            self::CAMPUSES_VIEW => 'View campus locations',
            self::CAMPUSES_MANAGE => 'Manage campuses',
            self::ZONES_VIEW => 'View operational zones',
            self::ZONES_MANAGE => 'Manage operational zones',
            self::BUILDINGS_VIEW => 'View buildings',
            self::BUILDINGS_MANAGE => 'Manage buildings',
            self::ROOMS_VIEW => 'View rooms',
            self::ROOMS_MANAGE => 'Manage rooms',
            self::AUDIT_VIEW => 'View administrative audit events',
        ];
    }
}
