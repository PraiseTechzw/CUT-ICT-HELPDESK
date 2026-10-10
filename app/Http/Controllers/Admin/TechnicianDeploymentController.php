<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveTechnicianDeploymentRequest;
use App\Models\OperationalZone;
use App\Models\TechnicianDeployment;
use App\Models\User;
use App\Support\AuditLogger;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TechnicianDeploymentController extends Controller
{
    public function index(): View
    {
        Gate::authorize(Permissions::TEAMS_MANAGE);
        $deployments = TechnicianDeployment::query()
            ->with(['technician', 'operationalZone.campus'])
            ->orderByDesc('starts_on')
            ->orderBy('id')
            ->paginate(20);

        return view('admin.technician-deployments.index', compact('deployments'));
    }

    public function create(): View
    {
        Gate::authorize(Permissions::TEAMS_MANAGE);

        return view('admin.technician-deployments.form', [
            'deployment' => new TechnicianDeployment,
            'technicians' => $this->technicians(),
            'zones' => OperationalZone::query()->with('campus')->orderBy('name')->get(),
        ]);
    }

    public function store(SaveTechnicianDeploymentRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validated();
        $deployment = DB::transaction(function () use ($data, $request, $auditLogger): TechnicianDeployment {
            $deployment = TechnicianDeployment::query()->create($data);
            $auditLogger->record($request->user(), 'technician_deployment.created', $deployment, [
                'fields' => ['technician_id', 'operational_zone_id', 'starts_on', 'ends_on'],
            ]);

            return $deployment;
        });

        return redirect()->route('admin.technician-deployments.index')->with('status', 'Technician deployment recorded.');
    }

    public function edit(TechnicianDeployment $deployment): View
    {
        Gate::authorize(Permissions::TEAMS_MANAGE);

        return view('admin.technician-deployments.form', [
            'deployment' => $deployment->load(['technician', 'operationalZone']),
            'technicians' => $this->technicians($deployment->technician),
            'zones' => OperationalZone::query()->with('campus')->orderBy('name')->get(),
        ]);
    }

    public function update(
        SaveTechnicianDeploymentRequest $request,
        TechnicianDeployment $deployment,
        AuditLogger $auditLogger
    ): RedirectResponse {
        $data = $request->validated();
        DB::transaction(function () use ($data, $request, $deployment, $auditLogger): void {
            $deployment->update($data);
            $auditLogger->record($request->user(), 'technician_deployment.updated', $deployment, [
                'fields' => array_keys($data),
            ]);
        });

        return redirect()->route('admin.technician-deployments.index')->with('status', 'Technician deployment updated.');
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
