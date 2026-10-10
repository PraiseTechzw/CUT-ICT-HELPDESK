<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AuditEventFilterRequest;
use App\Models\AuditEvent;
use App\Support\Permissions;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AuditEventController extends Controller
{
    public function index(AuditEventFilterRequest $request): View
    {
        Gate::authorize(Permissions::AUDIT_VIEW);
        $filters = $request->validated();
        $events = AuditEvent::query()
            ->with('actor:id,name')
            ->when($filters['action'] ?? null, fn ($query, string $action) => $query->where('action', 'like', '%'.$action.'%'))
            ->when($filters['actor'] ?? null, fn ($query, string $actor) => $query->whereHas(
                'actor',
                fn ($actorQuery) => $actorQuery->where('name', 'like', '%'.$actor.'%'),
            ))
            ->when($filters['from'] ?? null, fn ($query, string $from) => $query->where(
                'created_at',
                '>=',
                CarbonImmutable::createFromFormat('!Y-m-d', $from, (string) config('app.timezone'))->utc(),
            ))
            ->when($filters['through'] ?? null, fn ($query, string $through) => $query->where(
                'created_at',
                '<',
                CarbonImmutable::createFromFormat('!Y-m-d', $through, (string) config('app.timezone'))->addDay()->utc(),
            ))
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.audit.index', compact('events', 'filters'));
    }
}
