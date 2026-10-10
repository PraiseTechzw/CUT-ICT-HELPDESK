<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditLogger
{
    /** @param array<string, scalar|null|array> $metadata */
    public function record(?User $actor, string $action, Model $subject, array $metadata = []): void
    {
        AuditEvent::query()->create([
            'actor_id' => $actor?->getKey(),
            'action' => $action,
            'subject_type' => $subject::class,
            'subject_id' => (string) $subject->getKey(),
            'metadata' => $metadata,
        ]);
    }
}
