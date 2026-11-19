<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AuditLogger
{
    /**
     * Append an audit event. Source metadata is captured automatically for web
     * requests; no request (and therefore no IP/user-agent) is stored for
     * console/queue contexts. Sensitive values are never accepted here.
     *
     * @param  array<string, scalar|null|array>  $metadata
     */
    public function record(?User $actor, string $action, ?Model $subject = null, array $metadata = []): void
    {
        $ipAddress = null;
        $userAgent = null;

        if (! app()->runningInConsole() || app()->environment('testing')) {
            $request = request();
            $ipAddress = $request->ip();
            $agent = $request->userAgent();
            $userAgent = is_string($agent) ? Str::limit($agent, 500) : null;
        }

        AuditEvent::query()->create([
            'actor_id' => $actor?->getKey(),
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject === null ? null : (string) $subject->getKey(),
            'metadata' => $metadata === [] ? null : $metadata,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }
}
