<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveCampusRequest;
use App\Models\Campus;
use App\Support\AuditLogger;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CampusController extends Controller
{
    public function index(): View
    {
        Gate::authorize(Permissions::CAMPUSES_VIEW);
        $campuses = Campus::query()->orderBy('name')->paginate(15);

        return view('admin.locations.campuses.index', compact('campuses'));
    }

    public function create(): View
    {
        Gate::authorize(Permissions::CAMPUSES_MANAGE);

        return view('admin.locations.campuses.form', ['campus' => new Campus]);
    }

    public function store(SaveCampusRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $campus = DB::transaction(function () use ($request, $auditLogger): Campus {
            $campus = Campus::query()->create([...$request->validated(), 'is_active' => $request->boolean('is_active', true)]);
            $auditLogger->record($request->user(), 'campus.created', $campus, ['fields' => ['name', 'code', 'description', 'is_active']]);

            return $campus;
        });

        return redirect()->route('admin.campuses.index')->with('status', 'Campus created.');
    }

    public function edit(Campus $campus): View
    {
        Gate::authorize(Permissions::CAMPUSES_MANAGE);

        return view('admin.locations.campuses.form', compact('campus'));
    }

    public function update(SaveCampusRequest $request, Campus $campus, AuditLogger $auditLogger): RedirectResponse
    {
        DB::transaction(function () use ($request, $campus, $auditLogger): void {
            $campus->update([...$request->validated(), 'is_active' => $request->boolean('is_active', $campus->is_active)]);
            $auditLogger->record($request->user(), 'campus.updated', $campus, ['fields' => array_keys($request->validated())]);
        });

        return redirect()->route('admin.campuses.index')->with('status', 'Campus updated.');
    }
}
