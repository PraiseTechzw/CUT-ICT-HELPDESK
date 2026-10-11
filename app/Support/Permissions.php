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

    public const TICKETS_CREATE = 'tickets.create';

    public const TICKETS_VIEW_OWN = 'tickets.view_own';

    public const TICKETS_REPLY_OWN = 'tickets.reply_own';

    public const TICKETS_REOPEN_OWN = 'tickets.reopen_own';

    public const TICKETS_VIEW_ALL = 'tickets.view_all';

    public const TICKETS_VIEW_INTERNAL = 'tickets.view_internal';

    public const TICKETS_TRIAGE = 'tickets.triage';

    public const TICKETS_ASSIGN = 'tickets.assign';

    public const TICKETS_VIEW_ASSIGNED = 'tickets.view_assigned';

    public const TICKETS_WORK_ASSIGNED = 'tickets.work_assigned';

    public const TICKETS_COMMENT_PUBLIC = 'tickets.comment_public';

    public const TICKETS_COMMENT_INTERNAL = 'tickets.comment_internal';

    public const TICKETS_CATALOGUES_MANAGE = 'tickets.catalogues.manage';

    public const TEAMS_MANAGE = 'teams.manage';

    public const USER_GROUPS_MANAGE = 'user_groups.manage';

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
            self::TICKETS_CREATE => 'Create support tickets',
            self::TICKETS_VIEW_OWN => 'View own tickets',
            self::TICKETS_REPLY_OWN => 'Reply publicly to own tickets',
            self::TICKETS_REOPEN_OWN => 'Reopen own resolved or closed tickets',
            self::TICKETS_VIEW_ALL => 'View all tickets for helpdesk triage',
            self::TICKETS_VIEW_INTERNAL => 'Read internal ticket work notes',
            self::TICKETS_TRIAGE => 'Triage tickets and manage ticket status',
            self::TICKETS_ASSIGN => 'Assign tickets to individual technicians',
            self::TICKETS_VIEW_ASSIGNED => 'View tickets assigned to the signed-in technician',
            self::TICKETS_WORK_ASSIGNED => 'Update status and work on assigned tickets',
            self::TICKETS_COMMENT_PUBLIC => 'Add requester-visible ticket replies',
            self::TICKETS_COMMENT_INTERNAL => 'Add internal ticket work notes',
            self::TICKETS_CATALOGUES_MANAGE => 'Configure ticket category and priority catalogues',
            self::TEAMS_MANAGE => 'Manage teams, technician memberships, and deployments',
            self::USER_GROUPS_MANAGE => 'Manage user groups and group memberships',
        ];
    }
}
