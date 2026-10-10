<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveRoomRequest;
use App\Models\Building;
use App\Models\Room;
use App\Support\AuditLogger;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function index(): View
    {
        Gate::authorize(Permissions::ROOMS_VIEW);
        $rooms = Room::query()->with(['building.campus'])->orderBy('name')->paginate(15);

        return view('admin.locations.rooms.index', compact('rooms'));
    }

    public function create(): View
    {
        Gate::authorize(Permissions::ROOMS_MANAGE);
        $room = new Room;
        $buildings = Building::query()->with('campus')->orderBy('name')->get();

        return view('admin.locations.rooms.form', compact('room', 'buildings'));
    }

    public function store(SaveRoomRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        DB::transaction(function () use ($request, $auditLogger): void {
            $room = Room::query()->create([...$request->validated(), 'is_active' => $request->boolean('is_active', true)]);
            $auditLogger->record($request->user(), 'room.created', $room, ['fields' => ['building_id', 'name', 'code', 'description', 'is_active']]);
        });

        return redirect()->route('admin.rooms.index')->with('status', 'Room created.');
    }

    public function edit(Room $room): View
    {
        Gate::authorize(Permissions::ROOMS_MANAGE);
        $buildings = Building::query()->with('campus')->orderBy('name')->get();

        return view('admin.locations.rooms.form', compact('room', 'buildings'));
    }

    public function update(SaveRoomRequest $request, Room $room, AuditLogger $auditLogger): RedirectResponse
    {
        DB::transaction(function () use ($request, $room, $auditLogger): void {
            $room->update([...$request->validated(), 'is_active' => $request->boolean('is_active', $room->is_active)]);
            $auditLogger->record($request->user(), 'room.updated', $room, ['fields' => array_keys($request->validated())]);
        });

        return redirect()->route('admin.rooms.index')->with('status', 'Room updated.');
    }
}
