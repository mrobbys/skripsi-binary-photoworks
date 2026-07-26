<?php

namespace App\Domains\MasterData\Http\Controllers;

use App\Domains\MasterData\DTOs\PackageVariantData;
use App\Domains\MasterData\Http\Requests\PackageVariantRequest;
use App\Domains\MasterData\Models\PackageVariant;
use App\Domains\MasterData\Services\PackageVariantService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;

#[Middleware('permission:packageVariant-master-view', only: ['index'])]
#[Middleware('permission:packageVariant-master-create', only: ['store'])]
#[Middleware('permission:packageVariant-master-update', only: ['update', 'toggleActive'])]
#[Middleware('permission:packageVariant-master-delete', only: ['destroy'])]
class PackageVariantController extends Controller
{
	public function __construct(
		protected PackageVariantService $variantService,
	) {}

	/**
	 * Tampilkan semua variant paket dari paket tertenu
	 * @param Request $request
	 * @param string $packageSlug
	 */
	public function index(Request $request, string $packageSlug): JsonResponse
	{
		$limit = max(1, min((int) $request->query('limit', 10), 100));

		$variants = PackageVariant::with('features')
			->whereHas('package', fn($q) => $q->where('slug', $packageSlug))
			->orderBy('created_at', 'asc')
			->paginate($limit);

		return response()->json($variants);
	}

	/**
	 * Tambah varian baru
	 * @param PackageVariantRequest $request
	 * @param string $packageSlug
	 */
	public function store(PackageVariantRequest $request, string $packageSlug): JsonResponse
	{
		try {
			$variant = $this->variantService->createVariant($packageSlug, PackageVariantData::fromRequest($request));

			return $this->successResponse('Varian berhasil ditambahkan.', $variant, 201);
		} catch (\RuntimeException $e) {
			return $this->errorResponse($e->getMessage(), 422);
		} catch (\Exception $e) {
			return $this->errorResponse('Terjadi kesalahan server');
		}
	}

	/**
	 * Perbarui varian paket
	 * @param PackageVariantRequest $request
	 * @param PackageVariant $variant
	 */
	public function update(PackageVariantRequest $request, string $packageSlug, PackageVariant $variant): JsonResponse
	{
		try {
			$variant = $this->variantService->updateVariant($variant, PackageVariantData::fromRequest($request));

			return $this->successResponse('Varian berhasil diperbarui.', $variant);
		} catch (\RuntimeException $e) {
			return $this->errorResponse($e->getMessage(), 422);
		} catch (\Exception $e) {
			return $this->errorResponse('Terjadi kesalahan server');
		}
	}

	/**
	 * Hapus varian paket
	 * @param string $packageSlug = sengaja tidak digunakan didalam function, untuk menjaga parameter dari route
	 * @param PackageVariant $variant
	 */
	public function destroy(string $packageSlug, PackageVariant $variant): JsonResponse
	{
		try {
			$this->variantService->deleteVariant($variant);

			return $this->successResponse('Varian berhasil dihapus.');
		} catch (\RuntimeException $e) {
			return $this->errorResponse($e->getMessage(), 422);
		} catch (\Exception $e) {
			return $this->errorResponse('Terjadi kesalahan server');
		}
	}

	/**
	 * Toggle varian aktif
	 * @param string $packageSlug = sengaja tidak digunakan didalam function, untuk menjaga parameter dari route
	 * @param PackageVariant $variant
	 */
	public function toggleActive(string $packageSlug, PackageVariant $variant): JsonResponse
	{
		try {
			$this->variantService->toggleVariantActiveStatus($variant);

			return $this->successResponse('Status varian berhasil diperbarui.');
		} catch (\RuntimeException $e) {
			return $this->errorResponse($e->getMessage(), 422);
		} catch (\Exception $e) {
			return $this->errorResponse('Terjadi kesalahan server');
		}
	}
}
