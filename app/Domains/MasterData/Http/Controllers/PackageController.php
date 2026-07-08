<?php

namespace App\Domains\MasterData\Http\Controllers;

use App\Domains\MasterData\DTOs\PackageData;
use App\Domains\MasterData\Http\Requests\StorePackageRequest;
use App\Domains\MasterData\Http\Requests\UpdatePackageRequest;
use App\Domains\MasterData\Repositories\CategoryRepository;
use App\Domains\MasterData\Models\Package;
use App\Domains\MasterData\Repositories\PackageRepository;
use App\Domains\MasterData\Repositories\PackageVariantRepository;
use App\Domains\MasterData\Services\PackageService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function __construct(
        protected PackageService $packageService,
        protected CategoryRepository $categoryRepository,
        protected PackageRepository $packageRepository,
        protected PackageVariantRepository $variantRepository
    ) {}

    /**
     * Menampilkan daftar paket di halaman index
     * @param Request $request
     */
    public function index(Request $request): View|JsonResponse
    {
        $totalPackages = $this->packageRepository->countPackages();
        $totalActivePackages = $this->packageRepository->countActive();
        $totalActiveVariants = $this->variantRepository->countActive();

        if ($request->wantsJson()) {
            $search = $request->query('search');
            $limit = max(1, min((int) $request->query('limit', 10), 100));

            $query = Package::with(['category', 'features', 'variants'])
                ->withCount('variants')
                ->orderBy('created_at', 'desc');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $term = '%' . strtolower($search) . '%';
                    $q->whereRaw('LOWER(name) LIKE ?', [$term])
                        ->orWhereHas('category', function ($cq) use ($term) {
                            $cq->whereRaw('LOWER(name) LIKE ?', [$term]);
                        });
                });
            }

            $packages = $query->paginate($limit);

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
                'total_packages' => $totalPackages,
                'total_active_packages' => $totalActivePackages,
                'total_active_variants' => $totalActiveVariants,
            ]);
        }

        $categories = $this->categoryRepository->queryActive()
            ->orderBy('name')
            ->get(['id', 'category_code', 'name']);

        return view('backdoor.data-master.package.index', [
            'totalPackages' => $totalPackages,
            'totalActivePackages' => $totalActivePackages,
            'totalActiveVariants' => $totalActiveVariants,
            'categories' => $categories,
        ]);
    }

    /**
     * Menampilkan detail paket
     * @param string $slug
     */
    public function show(string $slug): View|JsonResponse
    {
        $package = Package::with(['category', 'features'])
            ->where('slug', $slug)
            ->firstOrFail();

        $categories = $this->categoryRepository->queryActive()
            ->orderBy('name')
            ->get(['id', 'category_code', 'name']);

        if (request()->wantsJson()) {
            return response()->json([
                'data' => $package,
            ]);
        }

        return view('backdoor.data-master.package.show', compact('package', 'categories'));
    }

    /**
     * Tambah data paket
     * @param StorePackageRequest $request
     */
    public function store(StorePackageRequest $request): JsonResponse
    {
        $package = $this->packageService->createPackage(PackageData::from($request));

        return response()->json([
            'status' => 'success',
            'message' => 'Paket berhasil ditambahkan.',
            'data' => $package,
        ], 201);
    }

    /**
     * Update data paket
     * @param UpdatePackageRequest $request
     * @param string $slug
     */
    public function update(UpdatePackageRequest $request, string $slug): JsonResponse
    {
        $package = $this->packageService->updatePackage($slug, PackageData::from($request));

        return response()->json([
            'status' => 'success',
            'message' => 'Paket berhasil diperbarui.',
            'data' => $package,
        ]);
    }

    /**
     * Hapus data paket
     * @param string $slug
     */
    public function destroy(string $slug): JsonResponse
    {
        $this->packageService->deletePackage($slug);

        return response()->json([
            'status' => 'success',
            'message' => 'Paket berhasil dihapus.',
        ]);
    }

    /**
     * Toggle status aktif paket
     * @param string $slug
     */
    public function toggleActive(string $slug): JsonResponse
    {
        $package = $this->packageService->toggleActiveStatus($slug);
        $totalActivePackages = $this->packageRepository->countActive();
        $totalActiveVariants = $this->variantRepository->countActive();

        return response()->json([
            'status' => 'success',
            'message' => 'Status paket berhasil diperbarui.',
            'data' => $package,
            'total_active_packages' => $totalActivePackages,
            'total_active_variants' => $totalActiveVariants,
        ]);
    }
}
