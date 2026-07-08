<?php

namespace App\Domains\MasterData\Http\Controllers;

use App\Domains\MasterData\DTOs\BackgroundData;
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

  /**
   * Tampilkan semua data background
   * @param Request $request
   */
  public function index(Request $request): View|JsonResponse
  {
    $totalActiveBackgrounds = $this->backgroundRepository->countActive();
    $totalBackgrounds = $this->backgroundRepository->countBackground();

    if ($request->wantsJson()) {
      $search = $request->query('search');
      $limit = max(1, min((int) $request->query('limit', 10), 100));

      $backgrounds = $this->backgroundRepository->searchQuery($search)->paginate($limit);

      $items = $backgrounds->through(fn(Background $bg) => [
        'id' => $bg->id,
        'name' => $bg->name,
        'description' => $bg->description,
        'is_active' => $bg->is_active,
        'image_url' => $bg->getFirstMediaUrl('background-image', 'thumb') ?: $bg->getFirstMediaUrl('background-image'),
        'original_url' => $bg->getFirstMediaUrl('background-image'),
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

  /**
   * Menambahkan data background
   * @param StoreBackgroundRequest $request
   */
  public function store(StoreBackgroundRequest $request): JsonResponse
  {
    $background = $this->backgroundService->createBackground(
      BackgroundData::from($request),
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
        'image_url' => $background->getFirstMediaUrl('background-image', 'thumb') ?: $background->getFirstMediaUrl('background-image'),
        'original_url' => $background->getFirstMediaUrl('background-image'),
      ],
    ], 201);
  }

  /**
   * Memperbarui data background
   * @param UpdateBackgroundRequest $request
   */
  public function update(UpdateBackgroundRequest $request, Background $background): JsonResponse
  {
    $updated = $this->backgroundService->updateBackground(
      $background->id,
      BackgroundData::from($request),
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
        'image_url' => $updated->getFirstMediaUrl('background-image', 'thumb') ?: $updated->getFirstMediaUrl('background-image'),
        'original_url' => $updated->getFirstMediaUrl('background-image'),
      ],
    ]);
  }

  /**
   * Menghapus data background
   * @param Background $background
   */
  public function destroy(Background $background): JsonResponse
  {
    $this->backgroundService->deleteBackground($background->id);

    return response()->json([
      'status' => 'success',
      'message' => 'Background berhasil dihapus.',
    ]);
  }

  /**
   * Mengubah status background
   * @param Background $background
   */
  public function toggleActive(Background $background): JsonResponse
  {
    $updated = $this->backgroundService->toggleActiveStatus($background->id);
    $totalActiveBackgrounds = $this->backgroundRepository->countActive();

    return response()->json([
      'status' => 'success',
      'message' => 'Status background berhasil diperbarui.',
      'data' => $updated,
      'total_active_backgrounds' => $totalActiveBackgrounds,
    ]);
  }
}
