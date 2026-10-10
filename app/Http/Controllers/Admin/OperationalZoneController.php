<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveOperationalZoneRequest;
use App\Models\Campus;
use App\Models\OperationalZone;
use App\Support\AuditLogger;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class OperationalZoneController extends Controller
{
    public function index(): View
    {
        Gate::authorize(Permissions::ZONES_VIEW);
        $zones = OperationalZone::query()->with('campus')->orderBy('name')->paginate(15);

        return view('admin.locations.zones.index', compact('zones'));
    }

    public function create(): View
    {
        Gate::authorize(Permissions::ZONES_MANAGE);

        return view('admin.locations.zones.form', ['zone' => new OperationalZone, 'campuses' => Campus::query()->orderBy('name')->get()]);
    }

    public function store(SaveOperationalZoneRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $zone = DB::transaction(function () use ($request, $auditLogger): OperationalZone {
            $zone = OperationalZone::query()->create([...$request->validated(), 'is_active' => $request->boolean('is_active', true)]);
            $auditLogger->record($request->user(), 'zone.created', $zone, ['fields' => ['campus_id', 'name', 'code', 'description', 'is_active']]);

            return $zone;
        });

        return redirect()->route('admin.zones.index')->with('status', 'Operational zone created.');
    }

    public function edit(OperationalZone $zone): View
    {
        Gate::authorize(Permissions::ZONES_MANAGE);
        $campuses = Campus::query()->orderBy('name')->get();

        return view('admin.locations.zones.form', compact('zone', 'campuses'));
    }

    public function update(SaveOperationalZoneRequest $request, OperationalZone $zone, AuditLogger $auditLogger): RedirectResponse
    {
        DB::transaction(function () use ($request, $zone, $auditLogger): void {
            $zone->update([...$request->validated(), 'is_active' => $request->boolean('is_active', $zone->is_active)]);
            $auditLogger->record($request->user(), 'zone.updated', $zone, ['fields' => array_keys($request->validated())]);
        });

        return redirect()->route('admin.zones.index')->with('status', 'Operational zone updated.');
    }
}
