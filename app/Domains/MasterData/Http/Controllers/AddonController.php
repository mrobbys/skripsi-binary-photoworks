<?php

namespace App\Domains\MasterData\Http\Controllers;

use App\Domains\MasterData\DTOs\AddonData;
use App\Domains\MasterData\Http\Requests\StoreAddonRequest;
use App\Domains\MasterData\Http\Requests\UpdateAddonRequest;
use App\Domains\MasterData\Models\Addon;
use App\Domains\MasterData\Repositories\AddonRepository;
use App\Domains\MasterData\Services\AddonService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\View\View;

class AddonController extends Controller
{
  public function __construct(
    protected AddonService $addonService,
    protected AddonRepository $addonRepository,
  ) {}

  /**
   * Tampil semua data add-on
   * @param Request $request
   */
  #[Middleware('permission:addon-master-view')]
  public function index(Request $request): View|JsonResponse
  {
    $totalAddons       = $this->addonRepository->countAddon();
    $totalActiveAddons = $this->addonRepository->countActive();

    if ($request->wantsJson()) {
      $search = $request->query('search');
      $limit  = max(1, min((int) $request->query('limit', 10), 100));

      $addons = $this->addonRepository->searchQuery($search)->paginate($limit);

      return response()->json([
        'data'                => $addons->items(),
        'current_page'        => $addons->currentPage(),
        'last_page'           => $addons->lastPage(),
        'total'               => $addons->total(),
        'total_addons'        => $totalAddons,
        'total_active_addons' => $totalActiveAddons,
      ]);
    }

    return view('backdoor.data-master.addon.index', [
      'totalAddons'       => $totalAddons,
      'totalActiveAddons' => $totalActiveAddons,
    ]);
  }

  /**
   * Menambahkan data add-on
   * @param StoreAddonRequest $request
   */
  #[Middleware('permission:addon-master-create')]
  public function store(StoreAddonRequest $request): JsonResponse
  {
    $addon = $this->addonService->createAddon(AddonData::from($request));

    return response()->json([
      'status'  => 'success',
      'message' => 'Add-on berhasil ditambahkan.',
      'data'    => $addon,
    ], 201);
  }

  /**
   * Memperbarui data add-on
   * @param UpdateAddonRequest $request
   * @param Addon $addon
   */
  #[Middleware('permission:addon-master-update')]
  public function update(UpdateAddonRequest $request, Addon $addon): JsonResponse
  {
    $updated = $this->addonService->updateAddon($addon->id, AddonData::from($request));

    return response()->json([
      'status'  => 'success',
      'message' => 'Add-on berhasil diperbarui.',
      'data'    => $updated,
    ]);
  }

  /**
   * Menghapus data add-on
   * @param Addon $addon
   */
  #[Middleware('permission:addon-master-delete')]
  public function destroy(Addon $addon): JsonResponse
  {
    $this->addonService->deleteAddon($addon->id);

    return response()->json([
      'status'  => 'success',
      'message' => 'Add-on berhasil dihapus.',
    ]);
  }

  /**
   * Mengubah status add-on
   * @param Addon $addon
   */
  #[Middleware('permission:addon-master-update')]
  public function toggleActive(Addon $addon): JsonResponse
  {
    $updated           = $this->addonService->toggleActiveStatus($addon->id);
    $totalActiveAddons = $this->addonRepository->countActive();

    return response()->json([
      'status'              => 'success',
      'message'             => 'Status add-on berhasil diperbarui.',
      'data'                => $updated,
      'total_active_addons' => $totalActiveAddons,
    ]);
  }
}
