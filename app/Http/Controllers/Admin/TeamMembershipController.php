<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveTechnicianTeamMembershipRequest;
use App\Models\Team;
use App\Models\TechnicianTeamMembership;
use App\Models\User;
use App\Services\TechnicianTeamMembershipService;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TeamMembershipController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize(Permissions::TEAMS_MANAGE);
        $teamId = filter_var($request->query('team_id'), FILTER_VALIDATE_INT) ?: null;
        $memberships = TechnicianTeamMembership::query()
            ->with(['technician', 'team'])
            ->when($teamId !== null, fn ($query) => $query->where('team_id', $teamId))
            ->orderByDesc('starts_on')
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.team-memberships.index', compact('memberships', 'teamId'));
    }

    public function create(Request $request): View
    {
        Gate::authorize(Permissions::TEAMS_MANAGE);
        $membership = new TechnicianTeamMembership;
        $membership->team_id = filter_var($request->query('team_id'), FILTER_VALIDATE_INT) ?: null;

        return view('admin.team-memberships.form', [
            'membership' => $membership,
            'teams' => Team::query()->orderBy('name')->get(),
            'technicians' => $this->technicians(),
        ]);
    }

    public function store(
        SaveTechnicianTeamMembershipRequest $request,
        TechnicianTeamMembershipService $service
    ): RedirectResponse {
        $membership = $service->save(null, $request->validated(), $request->user());

        return redirect()->route('admin.team-memberships.index', ['team_id' => $membership->team_id])
            ->with('status', 'Technician team membership recorded.');
    }

    public function edit(TechnicianTeamMembership $membership): View
    {
        Gate::authorize(Permissions::TEAMS_MANAGE);

        return view('admin.team-memberships.form', [
            'membership' => $membership->load(['team', 'technician']),
            'teams' => Team::query()->orderBy('name')->get(),
            'technicians' => $this->technicians($membership->technician),
        ]);
    }

    public function update(
        SaveTechnicianTeamMembershipRequest $request,
        TechnicianTeamMembership $membership,
        TechnicianTeamMembershipService $service
    ): RedirectResponse {
        $membership = $service->save($membership, $request->validated(), $request->user());

        return redirect()->route('admin.team-memberships.index', ['team_id' => $membership->team_id])
            ->with('status', 'Technician team membership updated.');
    }

    /** @return Collection<int, User> */
    private function technicians(?User $current = null): Collection
    {
        return User::query()
            ->whereHas('roles', fn ($query) => $query->where('slug', Roles::TECHNICIAN))
            ->when($current !== null, fn ($query) => $query->orWhereKey($current->getKey()))
            ->orderBy('name')
            ->get();
    }
}
