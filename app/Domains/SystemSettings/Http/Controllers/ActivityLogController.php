<?php

namespace App\Domains\SystemSettings\Http\Controllers;

use App\Domains\SystemSettings\DTOs\ActivityLogRowData;
use App\Domains\User\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
  /**
   * Halaman index activity log.
   */
  public function index(): View
  {
    return view('backdoor.system-settings.activity-logs.index');
  }

  /**
   * JSON endpoint untuk table.
   */
  public function data(Request $request): JsonResponse
  {
    $search = $request->input('search', '');
    $limit  = max(1, min($request->integer('limit', 10), 100));

    $query = Activity::with('causer')
      ->when($search, function ($q) use ($search) {
        $q->where(function ($inner) use ($search) {
          $inner->where('description', 'ilike', "%{$search}%")
            ->orWhereHasMorph(
              'causer',
              [User::class],
              fn($u) => $u->where('name', 'ilike', "%{$search}%")
            );
        });
      })
      ->latest('created_at');

    $paginated = $query->paginate($limit);

    return response()->json([
      'data' => ActivityLogRowData::collect($paginated->items()),
      'current_page' => $paginated->currentPage(),
      'last_page' => $paginated->lastPage(),
      'total' => $paginated->total(),
    ]);
  }
}
