<?php

namespace App\Domains\MasterData\Http\Controllers;

use App\Domains\MasterData\DTOs\AddonData;
use App\Domains\MasterData\Http\Requests\StoreAddonRequest;
use App\Domains\MasterData\Http\Requests\UpdateAddonRequest;
use App\Domains\MasterData\Models\Addon;
use App\Domains\MasterData\Services\AddonService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\View\View;

#[Middleware('permission:addon-master-view', only: ['index', 'data'])]
#[Middleware('permission:addon-master-create', only: ['store'])]
#[Middleware('permission:addon-master-update', only: ['update', 'toggleActive'])]
#[Middleware('permission:addon-master-delete', only: ['destroy'])]
class AddonController extends Controller
{
	public function __construct(
		protected AddonService $addonService,
	) {}

	public function index(): View
	{
		return view('backdoor.data-master.addon.index');
	}

	public function data(Request $request): JsonResponse
	{
		$search = $request->query('search');
		$limit = max(1, min((int) $request->query('limit', 10), 100));

		$addons = $this->addonService->searchQuery($search)->paginate($limit);

		return response()->json([
			'data' => $addons->items(),
			'current_page' => $addons->currentPage(),
			'last_page' => $addons->lastPage(),
			'total' => $addons->total(),
			'total_addons' => Addon::count(),
			'total_active_addons' => Addon::where('is_active', true)->count(),
		]);
	}

	/**
	 * Menambahkan data add-on
	 * @param StoreAddonRequest $request
	 */
	public function store(StoreAddonRequest $request): JsonResponse
	{
		try {
			$addon = $this->addonService->createAddon(AddonData::fromRequest($request));

			return $this->successResponse('Add-on berhasil ditambahkan.', $addon, 201);
		} catch (\RuntimeException $e) {
			return $this->errorResponse($e->getMessage(), 422);
		} catch (\Exception $e) {
			return $this->errorResponse('Terjadi kesalahan server');
		}
	}

	/**
	 * Memperbarui data add-on
	 * @param UpdateAddonRequest $request
	 * @param Addon $addon
	 */
	public function update(UpdateAddonRequest $request, Addon $addon): JsonResponse
	{
		try {
			$updated = $this->addonService->updateAddon($addon->id, AddonData::fromRequest($request));

			return $this->successResponse('Add-on berhasil diperbarui.', $updated);
		} catch (\RuntimeException $e) {
			return $this->errorResponse($e->getMessage(), 422);
		} catch (\Exception $e) {
			return $this->errorResponse('Terjadi kesalahan server');
		}
	}

	/**
	 * Menghapus data add-on
	 * @param Addon $addon
	 */
	public function destroy(Addon $addon): JsonResponse
	{
		try {
			$this->addonService->deleteAddon($addon->id);

			return $this->successResponse('Add-on berhasil dihapus.');
		} catch (\RuntimeException $e) {
			return $this->errorResponse($e->getMessage(), 422);
		} catch (\Exception $e) {
			return $this->errorResponse('Terjadi kesalahan server');
		}
	}

	/**
	 * Mengubah status add-on
	 * @param Addon $addon
	 */
	public function toggleActive(Addon $addon): JsonResponse
	{
		try {
			$this->addonService->toggleActiveStatus($addon->id);

			return $this->successResponse('Status add-on berhasil diperbarui.');
		} catch (\RuntimeException $e) {
			return $this->errorResponse($e->getMessage(), 422);
		} catch (\Exception $e) {
			return $this->errorResponse('Terjadi kesalahan server');
		}
	}
}
