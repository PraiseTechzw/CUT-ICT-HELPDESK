<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditEvent;
use App\Support\Permissions;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AuditEventController extends Controller
{
    public function index(): View
    {
        Gate::authorize(Permissions::AUDIT_VIEW);
        $events = AuditEvent::query()
            ->with('actor:id,name,email')
            ->orderByDesc('id')
            ->paginate(25);

        return view('admin.audit.index', compact('events'));
    }
}
