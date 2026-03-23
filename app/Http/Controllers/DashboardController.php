<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Dashboard', [
            'role' => $user->getRoleNames()->first() ?? 'membre',
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'hasCompletedOnboarding' => $user->has_completed_onboarding ?? false,
        ]);
    }
}
