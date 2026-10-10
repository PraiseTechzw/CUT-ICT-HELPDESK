<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveBuildingRequest;
use App\Models\Building;
use App\Models\Campus;
use App\Models\OperationalZone;
use App\Support\AuditLogger;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BuildingController extends Controller
{
    public function index(): View
    {
        Gate::authorize(Permissions::BUILDINGS_VIEW);
        $buildings = Building::query()->with(['campus', 'zone'])->orderBy('name')->paginate(15);

        return view('admin.locations.buildings.index', compact('buildings'));
    }

    public function create(): View
    {
        Gate::authorize(Permissions::BUILDINGS_MANAGE);
        $building = new Building;
        $campuses = Campus::query()->orderBy('name')->get();
        $zones = OperationalZone::query()->with('campus')->orderBy('name')->get();

        return view('admin.locations.buildings.form', compact('building', 'campuses', 'zones'));
    }

    public function store(SaveBuildingRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $building = DB::transaction(function () use ($request, $auditLogger): Building {
            $building = Building::query()->create([...$request->validated(), 'is_active' => $request->boolean('is_active', true)]);
            $auditLogger->record($request->user(), 'building.created', $building, ['fields' => ['campus_id', 'operational_zone_id', 'name', 'code', 'description', 'is_active']]);

            return $building;
        });

        return redirect()->route('admin.buildings.index')->with('status', 'Building created.');
    }

    public function edit(Building $building): View
    {
        Gate::authorize(Permissions::BUILDINGS_MANAGE);
        $campuses = Campus::query()->orderBy('name')->get();
        $zones = OperationalZone::query()->with('campus')->orderBy('name')->get();

        return view('admin.locations.buildings.form', compact('building', 'campuses', 'zones'));
    }

    public function update(SaveBuildingRequest $request, Building $building, AuditLogger $auditLogger): RedirectResponse
    {
        DB::transaction(function () use ($request, $building, $auditLogger): void {
            $building->update([...$request->validated(), 'is_active' => $request->boolean('is_active', $building->is_active)]);
            $auditLogger->record($request->user(), 'building.updated', $building, ['fields' => array_keys($request->validated())]);
        });

        return redirect()->route('admin.buildings.index')->with('status', 'Building updated.');
    }
}
