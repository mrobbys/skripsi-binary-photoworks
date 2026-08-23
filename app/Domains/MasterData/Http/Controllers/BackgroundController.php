<?php

namespace App\Domains\MasterData\Http\Controllers;

use App\Domains\MasterData\DTOs\BackgroundData;
use App\Domains\MasterData\Http\Requests\StoreBackgroundRequest;
use App\Domains\MasterData\Http\Requests\UpdateBackgroundRequest;
use App\Domains\MasterData\Models\Background;
use App\Domains\MasterData\Services\BackgroundService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\View\View;

#[Middleware('permission:background-master-view', only: ['index', 'data'])]
#[Middleware('permission:background-master-create', only: ['store'])]
#[Middleware('permission:background-master-update', only: ['update', 'toggleActive'])]
#[Middleware('permission:background-master-delete', only: ['destroy'])]
class BackgroundController extends Controller
{
	public function __construct(
		protected BackgroundService $backgroundService,
	) {}

	public function index(): View
	{
		return view('backdoor.data-master.background.index');
	}

	public function data(Request $request): JsonResponse
	{
		$search = $request->query('search');
		$limit = max(1, min((int) $request->query('limit', 10), 100));

		$backgrounds = $this->backgroundService->searchQuery($search)->paginate($limit);

		$items = $backgrounds->through(fn($bg) => [
			'id' => $bg->id,
			'name' => $bg->name,
			'description' => $bg->description,
			'is_active' => $bg->is_active,
			'image_url' => $bg->getFirstMediaUrl('background-image', 'thumb') ?: $bg->getFirstMediaUrl('background-image'),
			'original_url' => $bg->getFirstMediaUrl('background-image', 'webp') ?: $bg->getFirstMediaUrl('background-image'),
			'created_at' => $bg->created_at,
		]);

		return response()->json([
			'data' => $items->items(),
			'current_page' => $backgrounds->currentPage(),
			'last_page' => $backgrounds->lastPage(),
			'total' => $backgrounds->total(),
			'total_active_backgrounds' => Background::where('is_active', true)->count(),
			'total_backgrounds' => Background::count(),
		]);
	}

	public function store(StoreBackgroundRequest $request): JsonResponse
	{
		try {
			$background = $this->backgroundService->createBackground(
				BackgroundData::fromRequest($request),
				$request->file('image'),
			);

			return $this->successResponse('Background berhasil ditambahkan.', $background, 201);
		} catch (\Exception $e) {
			return $this->errorResponse('Terjadi kesalahan server');
		}
	}

	public function update(UpdateBackgroundRequest $request, Background $background): JsonResponse
	{
		try {
			$updated = $this->backgroundService->updateBackground(
				$background->id,
				BackgroundData::fromRequest($request),
				$request->file('image'),
			);

			return $this->successResponse('Background berhasil diperbarui.', $updated);
		} catch (\Exception $e) {
			return $this->errorResponse('Terjadi kesalahan server');
		}
	}

	public function destroy(Background $background): JsonResponse
	{
		try {
			$this->backgroundService->deleteBackground($background->id);

			return $this->successResponse('Background berhasil dihapus.');
		} catch (\Exception $e) {
			return $this->errorResponse('Terjadi kesalahan server');
		}
	}

	public function toggleActive(Background $background): JsonResponse
	{
		try {
			$this->backgroundService->toggleActiveStatus($background->id);

			return $this->successResponse('Status background berhasil diperbarui.');
		} catch (\Exception $e) {
			return $this->errorResponse('Terjadi kesalahan server');
		}
	}
}
