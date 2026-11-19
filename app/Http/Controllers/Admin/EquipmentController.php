<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Permissions;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class EquipmentController extends Controller
{
    public function index(): View
    {
        Gate::authorize(Permissions::USERS_VIEW);

        return view('admin.equipment.index');
    }
}
