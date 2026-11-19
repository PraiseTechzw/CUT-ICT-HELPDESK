<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(): View
    {
        $user = request()->user()->load(['roles', 'userGroups']);

        return view('account.index', [
            'user' => $user,
            'notificationCount' => $user->notifications()->count(),
            'unreadNotificationCount' => $user->unreadNotifications()->count(),
        ]);
    }

    public function updatePassword(UpdatePasswordRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $user = $request->user();
        $user->update(['password' => $request->validated('password')]);

        $auditLogger->record($user, 'user.password_changed', $user);

        return back()->with('status', 'Your password has been changed.');
    }
}
