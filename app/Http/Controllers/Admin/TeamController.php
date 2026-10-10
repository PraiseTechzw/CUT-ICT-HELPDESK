<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveTeamRequest;
use App\Models\Team;
use App\Support\AuditLogger;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function index(): View
    {
        Gate::authorize(Permissions::TEAMS_MANAGE);
        $teams = Team::query()->withCount('memberships')->orderBy('name')->paginate(20);

        return view('admin.teams.index', compact('teams'));
    }

    public function create(): View
    {
        Gate::authorize(Permissions::TEAMS_MANAGE);

        return view('admin.teams.form', ['team' => new Team]);
    }

    public function store(SaveTeamRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);
        $team = DB::transaction(function () use ($data, $request, $auditLogger): Team {
            $team = Team::query()->create($data);
            $auditLogger->record($request->user(), 'team.created', $team, [
                'fields' => ['name', 'slug', 'description', 'is_active'],
            ]);

            return $team;
        });

        return redirect()->route('admin.teams.index')->with('status', 'Team created.');
    }

    public function edit(Team $team): View
    {
        Gate::authorize(Permissions::TEAMS_MANAGE);

        return view('admin.teams.form', compact('team'));
    }

    public function update(SaveTeamRequest $request, Team $team, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', $team->is_active);
        DB::transaction(function () use ($data, $request, $team, $auditLogger): void {
            $team->update($data);
            $auditLogger->record($request->user(), 'team.updated', $team, [
                'fields' => array_keys($data),
            ]);
        });

        return redirect()->route('admin.teams.index')->with('status', 'Team updated.');
    }
}
