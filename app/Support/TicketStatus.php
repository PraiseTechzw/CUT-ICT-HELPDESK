<?php

declare(strict_types=1);

namespace App\Support;

final class TicketStatus
{
    public const OPEN = 'open';

    public const ASSIGNED = 'assigned';

    public const IN_PROGRESS = 'in_progress';

    public const WAITING_FOR_REQUESTER = 'waiting_for_requester';

    public const RESOLVED = 'resolved';

    public const CLOSED = 'closed';

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            self::OPEN => 'Open',
            self::ASSIGNED => 'Assigned',
            self::IN_PROGRESS => 'In Progress',
            self::WAITING_FOR_REQUESTER => 'Waiting on Requester',
            self::RESOLVED => 'Resolved',
            self::CLOSED => 'Closed',
        ];
    }

    /** @return array<string, string> */
    public static function descriptions(): array
    {
        return [
            self::OPEN => 'Received and waiting for triage.',
            self::ASSIGNED => 'Assigned to an individual technician.',
            self::IN_PROGRESS => 'A technician is actively working on the ticket.',
            self::WAITING_FOR_REQUESTER => 'Support is waiting for information from the requester.',
            self::RESOLVED => 'A solution has been provided.',
            self::CLOSED => 'The ticket is complete and closed.',
        ];
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_keys(self::labels());
    }

    /** @return array<string, list<string>> */
    public static function transitions(): array
    {
        return [
            self::OPEN => [],
            self::ASSIGNED => [self::IN_PROGRESS],
            self::IN_PROGRESS => [self::WAITING_FOR_REQUESTER, self::RESOLVED],
            self::WAITING_FOR_REQUESTER => [self::IN_PROGRESS, self::RESOLVED],
            self::RESOLVED => [self::CLOSED],
            self::CLOSED => [],
        ];
    }

    private function __construct() {}
}
