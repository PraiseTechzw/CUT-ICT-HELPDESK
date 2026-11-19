<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Support\AuditLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * Records authentication outcomes in the append-only audit trail.
 *
 * Credentials, submitted email addresses and tokens are deliberately never
 * copied into metadata — only the event type and request source are stored.
 */
class RecordAuthenticationAudit
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function login(Login $event): void
    {
        $this->auditLogger->record($event->user, 'auth.login', $event->user);
    }

    public function logout(Logout $event): void
    {
        $this->auditLogger->record($event->user, 'auth.logout', $event->user);
    }

    public function failed(Failed $event): void
    {
        $this->auditLogger->record(null, 'auth.login.failed', null, ['guard' => $event->guard]);
    }

    public function lockout(Lockout $event): void
    {
        $this->auditLogger->record(null, 'auth.login.lockout', null, []);
    }

    public function passwordReset(PasswordReset $event): void
    {
        $this->auditLogger->record($event->user, 'auth.password_reset', $event->user);
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(Login::class, [self::class, 'login']);
        $events->listen(Logout::class, [self::class, 'logout']);
        $events->listen(Failed::class, [self::class, 'failed']);
        $events->listen(Lockout::class, [self::class, 'lockout']);
        $events->listen(PasswordReset::class, [self::class, 'passwordReset']);
    }
}
