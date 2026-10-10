<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\TechnicianTeamMembership;
use App\Models\User;
use App\Support\AuditLogger;
use App\Support\Roles;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TechnicianTeamMembershipService
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /** @param array{technician_id: int, team_id: int, starts_on: string, ends_on: ?string} $data */
    public function save(?TechnicianTeamMembership $membership, array $data, User $actor): TechnicianTeamMembership
    {
        $data['ends_on'] = $data['ends_on'] ?? null;

        return DB::transaction(function () use ($membership, $data, $actor): TechnicianTeamMembership {
            $technicianIds = collect([$membership?->technician_id, $data['technician_id']])
                ->filter()
                ->unique()
                ->sort()
                ->values();
            $lockedUsers = User::query()
                ->whereIn('id', $technicianIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();
            $technician = $lockedUsers->firstWhere('id', $data['technician_id']);

            if ($technician === null || ! $technician->hasRole(Roles::TECHNICIAN)) {
                throw ValidationException::withMessages([
                    'technician_id' => 'Choose an account that has the Technician role.',
                ]);
            }

            $overlap = TechnicianTeamMembership::query()
                ->where('technician_id', $data['technician_id'])
                ->where('team_id', $data['team_id'])
                ->when($membership !== null, fn ($query) => $query->where('id', '!=', $membership->getKey()))
                ->when($data['ends_on'] !== null, fn ($query) => $query->whereDate('starts_on', '<=', $data['ends_on']))
                ->where(function ($query) use ($data): void {
                    $query->whereNull('ends_on')->orWhereDate('ends_on', '>=', $data['starts_on']);
                })
                ->exists();

            if ($overlap) {
                throw ValidationException::withMessages([
                    'starts_on' => 'This technician already has a membership period for the selected team that overlaps these inclusive dates.',
                ]);
            }

            $created = $membership === null;
            $membership ??= new TechnicianTeamMembership;
            $membership->fill($data);
            $membership->save();
            $this->auditLogger->record($actor, $created ? 'team_membership.created' : 'team_membership.updated', $membership, [
                'fields' => ['technician_id', 'team_id', 'starts_on', 'ends_on'],
            ]);

            return $membership;
        });
    }
}
