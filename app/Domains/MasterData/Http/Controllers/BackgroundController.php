<?php

namespace App\Domains\MasterData\Http\Controllers;

use App\Domains\MasterData\Http\Requests\StoreBackgroundRequest;
use App\Domains\MasterData\Http\Requests\UpdateBackgroundRequest;
use App\Domains\MasterData\Models\Background;
use App\Domains\MasterData\Repositories\BackgroundRepository;
use App\Domains\MasterData\Services\BackgroundService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BackgroundController extends Controller
{
  public function __construct(
    protected BackgroundService $backgroundService,
    protected BackgroundRepository $backgroundRepository,
  ) {}

  public function index(Request $request): View|JsonResponse
  {
    $totalActiveBackgrounds = Background::where('is_active', true)->count();
    $totalBackgrounds = Background::count();

    if ($request->wantsJson()) {
      $search = $request->query('search');
      $limit = max(1, min((int) $request->query('limit', 10), 100));

      $backgrounds = $this->backgroundRepository->getPaginated($search, $limit);

      $items = $backgrounds->through(fn(Background $bg) => [
        'id' => $bg->id,
        'name' => $bg->name,
        'description' => $bg->description,
        'is_active' => $bg->is_active,
        'image_url' => $bg->getFirstMediaUrl('background-image'),
        'created_at' => $bg->created_at,
      ]);

      return response()->json([
        'data' => $items->items(),
        'current_page' => $backgrounds->currentPage(),
        'last_page' => $backgrounds->lastPage(),
        'total' => $backgrounds->total(),
        'total_active_backgrounds' => $totalActiveBackgrounds,
        'total_backgrounds' => $totalBackgrounds,
      ]);
    }

    return view('backdoor.data-master.background.index', [
      'totalActiveBackgrounds' => $totalActiveBackgrounds,
      'totalBackgrounds' => $totalBackgrounds,
    ]);
  }

  public function store(StoreBackgroundRequest $request): JsonResponse
  {
    $background = $this->backgroundService->createBackground(
      $request->toDto(),
      $request->file('image'),
    );

    return response()->json([
      'status' => 'success',
      'message' => 'Background berhasil ditambahkan.',
      'data' => [
        'id' => $background->id,
        'name' => $background->name,
        'description' => $background->description,
        'is_active' => $background->is_active,
        'image_url' => $background->getFirstMediaUrl('background-image'),
      ],
    ], 201);
  }

  public function update(UpdateBackgroundRequest $request, Background $background): JsonResponse
  {
    $updated = $this->backgroundService->updateBackground(
      $background->id,
      $request->toDto(),
      $request->file('image'),
    );

    return response()->json([
      'status' => 'success',
      'message' => 'Background berhasil diperbarui.',
      'data' => [
        'id' => $updated->id,
        'name' => $updated->name,
        'description' => $updated->description,
        'is_active' => $updated->is_active,
        'image_url' => $updated->getFirstMediaUrl('background-image'),
      ],
    ]);
  }

  public function destroy(Background $background): JsonResponse
  {
    $this->backgroundService->deleteBackground($background->id);

    return response()->json([
      'status' => 'success',
      'message' => 'Background berhasil dihapus.',
    ]);
  }

  public function toggleActive(Background $background): JsonResponse
  {
    $updated = $this->backgroundService->toggleActiveStatus($background->id);
    $totalActiveBackgrounds = Background::where('is_active', true)->count();

    return response()->json([
      'status' => 'success',
      'message' => 'Status background berhasil diperbarui.',
      'data' => $updated,
      'total_active_backgrounds' => $totalActiveBackgrounds,
    ]);
  }
}
