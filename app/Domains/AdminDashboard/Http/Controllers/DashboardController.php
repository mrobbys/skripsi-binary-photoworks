<?php

namespace App\Domains\AdminDashboard\Http\Controllers;

use App\Domains\AdminDashboard\Services\DashboardService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\View\View;

#[Middleware('permission:dashboard-admin-view', only: ['index', 'analytics'])]
class DashboardController extends Controller
{
  public function __construct(
    private readonly DashboardService $service,
  ) {}

  public function index(): View
  {
    $stats = $this->service->getStats();
    $today = $this->service->getTodaySchedule();
    $recent = $this->service->getRecentBookings();
    $availableYears = $this->service->getAvailableYears();

    return view('backdoor.dashboard.index', compact('stats', 'today', 'recent', 'availableYears'));
  }

  public function analytics(Request $request): JsonResponse
  {
    $year = $request->integer('year', (int) date('Y'));
    $data = $this->service->getAnalytics($year);

    return response()->json($data);
  }
}
