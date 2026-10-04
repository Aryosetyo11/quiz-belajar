<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

class PortalDashboardController extends Controller
{
    public function teacher(): View
    {
        return view('portal.dashboard', ['role' => User::ROLE_TEACHER]);
    }

    public function student(): View
    {
        return view('portal.dashboard', ['role' => User::ROLE_STUDENT]);
    }
}
