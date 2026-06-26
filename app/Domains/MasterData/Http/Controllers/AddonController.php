<?php

namespace App\Domains\MasterData\Http\Controllers;

use App\Domains\MasterData\Http\Requests\StoreAddonRequest;
use App\Domains\MasterData\Http\Requests\UpdateAddonRequest;
use App\Domains\MasterData\Models\Addon;
use App\Domains\MasterData\Repositories\AddonRepository;
use App\Domains\MasterData\Services\AddonService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AddonController extends Controller
{
  public function __construct(
    protected AddonService $addonService,
    protected AddonRepository $addonRepository,
  ) {}

  public function index(Request $request): View|JsonResponse
  {
    $totalAddons       = Addon::count();
    $totalActiveAddons = Addon::where('is_active', true)->count();

    if ($request->wantsJson()) {
      $search = $request->query('search');
      $limit  = max(1, min((int) $request->query('limit', 10), 100));

      $addons = $this->addonRepository->getPaginated($search, $limit);

      $items = $addons->through(fn(Addon $addon) => [
        'id'           => $addon->id,
        'name'         => $addon->name,
        'price'        => $addon->price,
        'description'  => $addon->description,
        'has_quantity' => $addon->has_quantity,
        'is_active'    => $addon->is_active,
        'created_at'   => $addon->created_at,
      ]);

      return response()->json([
        'data'                => $items->items(),
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

  public function store(StoreAddonRequest $request): JsonResponse
  {
    $addon = $this->addonService->createAddon($request->toDto());

    return response()->json([
      'status'  => 'success',
      'message' => 'Add-on berhasil ditambahkan.',
      'data'    => $addon,
    ], 201);
  }

  public function update(UpdateAddonRequest $request, Addon $addon): JsonResponse
  {
    $updated = $this->addonService->updateAddon($addon->id, $request->toDto());

    return response()->json([
      'status'  => 'success',
      'message' => 'Add-on berhasil diperbarui.',
      'data'    => $updated,
    ]);
  }

  public function destroy(Addon $addon): JsonResponse
  {
    $this->addonService->deleteAddon($addon->id);

    return response()->json([
      'status'  => 'success',
      'message' => 'Add-on berhasil dihapus.',
    ]);
  }

  public function toggleActive(Addon $addon): JsonResponse
  {
    $updated           = $this->addonService->toggleActiveStatus($addon->id);
    $totalActiveAddons = Addon::where('is_active', true)->count();

    return response()->json([
      'status'              => 'success',
      'message'             => 'Status add-on berhasil diperbarui.',
      'data'                => $updated,
      'total_active_addons' => $totalActiveAddons,
    ]);
  }
}
