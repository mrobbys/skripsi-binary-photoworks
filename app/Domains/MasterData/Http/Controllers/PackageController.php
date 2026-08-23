<?php

namespace App\Domains\MasterData\Http\Controllers;

use App\Domains\MasterData\DTOs\PackageData;
use App\Domains\MasterData\Http\Requests\StorePackageRequest;
use App\Domains\MasterData\Http\Requests\UpdatePackageRequest;
use App\Domains\MasterData\Models\Category;
use App\Domains\MasterData\Models\Package;
use App\Domains\MasterData\Models\PackageVariant;
use Illuminate\Database\Eloquent\Collection;
use App\Domains\MasterData\Services\PackageService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\View\View;

#[Middleware('permission:packageVariant-master-view', only: ['index', 'show', 'data', 'showInfo'])]
#[Middleware('permission:packageVariant-master-create', only: ['store'])]
#[Middleware('permission:packageVariant-master-update', only: ['update', 'toggleActive'])]
#[Middleware('permission:packageVariant-master-delete', only: ['destroy'])]
class PackageController extends Controller
{
    public function __construct(
        protected PackageService $packageService,
    ) {}

	public function index(): View
	{
		return view('backdoor.data-master.package.index', [
			'categories' => $this->activeCategories(),
		]);
	}

	/**
	 * Menampilkan data paket di halaman index
	 * @param Request $request
	 */
	public function data(Request $request): JsonResponse
	{
		$search = $request->query('search');
		$limit = max(1, min((int) $request->query('limit', 10), 100));

		$packages = $this->packageService->searchQuery($search)->paginate($limit);

		$items = collect($packages->items())->map(function (Package $package) {
			$activeVariants = $package->variants->where('is_active', true);
			$priceMin = $activeVariants->min('price');
			$priceMax = $activeVariants->max('price');

			return array_merge($package->toArray(), [
				'price_min' => $priceMin,
				'price_max' => $priceMax,
				'variants_count' => $package->variants_count,
			]);
		});

		return response()->json([
			'data' => $items,
			'current_page' => $packages->currentPage(),
			'last_page' => $packages->lastPage(),
			'total' => $packages->total(),
			'total_active_packages' => Package::where('is_active', true)->count(),
            'total_active_variants' => PackageVariant::where('is_active', true)->count(),
		]);
	}

	/**
	 * Menampilkan detail paket
	 * @param Package $package
	 */
	public function show(Package $package): View
	{
		$categories = $this->activeCategories();
		return view('backdoor.data-master.package.show', compact('categories', 'package'));
	}

	/**
	 * Mengambil data JSON detail paket
	 * @param string $slug
	 */
	public function showInfo(string $slug): JsonResponse
	{
		$package = Package::with(['category', 'features'])
			->where('slug', $slug)
			->firstOrFail();

		return response()->json([
			'data' => array_merge($package->toArray(), [
				'image_url' => $package->getFirstMediaUrl('package-image', 'webp') ?: $package->getFirstMediaUrl('package-image'),
				'features' => $package->features->pluck('description')->values()->all()
			]),
		]);
	}

	/**
	 * Tambah data paket
	 * @param StorePackageRequest $request
	 */
	public function store(StorePackageRequest $request): JsonResponse
	{
		try {
			$package = $this->packageService->createPackage(
				PackageData::fromRequest($request),
				$request->file('image'),
			);

			return $this->successResponse('Paket berhasil ditambahkan', $package, 201);
		} catch (\RuntimeException $e) {
			return $this->errorResponse($e->getMessage(), 422);
		} catch (\Exception $e) {
			return $this->errorResponse('Terjadi kesalahan server');
		}
	}

	/**
	 * Update data paket
	 * @param UpdatePackageRequest $request
	 * @param string $slug
	 */
	public function update(UpdatePackageRequest $request, string $slug): JsonResponse
	{
		try {
			$package = $this->packageService->updatePackage(
				$slug,
				PackageData::fromRequest($request),
				$request->file('image'),
			);

			return $this->successResponse('Paket berhasil diperbarui', $package);
		} catch (\RuntimeException $e) {
			return $this->errorResponse($e->getMessage(), 422);
		} catch (\Exception $e) {
			return $this->errorResponse('Terjadi kesalahan server');
		}
	}

	/**
	 * Hapus data paket
	 * @param string $slug
	 */
	public function destroy(string $slug): JsonResponse
	{
		try {
			$this->packageService->deletePackage($slug);

			return $this->successResponse('Paket berhasil dihapus');
		} catch (\RuntimeException $e) {
			return $this->errorResponse($e->getMessage(), 422);
		} catch (\Exception $e) {
			return $this->errorResponse('Terjadi kesalahan server');
		}
	}

	/**
	 * Toggle status aktif paket
	 * @param string $slug
	 */
	public function toggleActive(string $slug): JsonResponse
	{
		try {
			$this->packageService->toggleActiveStatus($slug);

			return $this->successResponse('Status paket berhasil diperbarui');
		} catch (\RuntimeException $e) {
			return $this->errorResponse($e->getMessage(), 422);
		} catch (\Exception $e) {
			return $this->errorResponse('Terjadi kesalahan server');
		}
	}

	/**
	 * Ambil kategori yang aktif
	 */
	private function activeCategories(): Collection
	{
		return Category::where('is_active', true)
			->orderBy('name')
			->get();
	}
}
