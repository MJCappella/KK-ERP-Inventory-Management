<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(protected DashboardService $dashboardService) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $stats = $this->dashboardService->getStats($user);

        return view('dashboard.index', compact('stats', 'user'));
    }
}
