<?php

declare(strict_types=1);

namespace App\Support;

final class Roles
{
    public const REQUESTER = 'requester';

    public const HELPDESK_OFFICER = 'helpdesk-officer';

    public const TECHNICIAN = 'technician';

    public const REGIONAL_SUPERVISOR = 'regional-supervisor';

    public const ICT_MANAGER = 'ict-manager';

    public const SYSTEM_ADMINISTRATOR = 'system-administrator';

    public const AUDITOR = 'auditor';

    private function __construct() {}
}
