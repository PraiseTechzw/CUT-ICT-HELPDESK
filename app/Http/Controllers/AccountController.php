<?php

declare(strict_types=1);

namespace App\Http\Controllers;

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
}
