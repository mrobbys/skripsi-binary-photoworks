<?php

namespace App\Domains\MasterData\Http\Controllers;

use App\Domains\MasterData\DTOs\PackageData;
use App\Domains\MasterData\Http\Requests\StorePackageRequest;
use App\Domains\MasterData\Http\Requests\UpdatePackageRequest;
use App\Domains\MasterData\Models\Category;
use App\Domains\MasterData\Models\Package;
use App\Domains\MasterData\Repositories\PackageRepository;
use App\Domains\MasterData\Repositories\PackageVariantRepository;
use App\Domains\MasterData\Services\PackageService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function __construct(
        protected PackageService $packageService,
        protected PackageRepository $packageRepository,
        protected PackageVariantRepository $variantRepository
    ) {}

    /**
     * Menampilkan daftar paket di halaman index
     * @param Request $request
     */
    #[Middleware('permission:package-variant-master-view')]
    public function index(Request $request): View|JsonResponse
    {
        $totalPackages = $this->packageRepository->countPackages();
        $totalActivePackages = $this->packageRepository->countActive();
        $totalActiveVariants = $this->variantRepository->countActive();

        if ($request->wantsJson()) {
            $search = $request->query('search');
            $limit = max(1, min((int) $request->query('limit', 10), 100));

            $packages = $this->packageRepository->searchQuery($search)->paginate($limit);

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

        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get();

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
    #[Middleware('permission:package-variant-master-view')]
    public function show(string $slug): View|JsonResponse
    {
        $package = Package::with(['category', 'features'])
            ->where('slug', $slug)
            ->firstOrFail();

        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get();

        if (request()->wantsJson()) {
            return response()->json([
                'data' => array_merge($package->toArray(), [
                    'image_url' => $package->getFirstMediaUrl('package-image'),
                ]),
            ]);
        }

        return view('backdoor.data-master.package.show', compact('package', 'categories'));
    }

    /**
     * Tambah data paket
     * @param StorePackageRequest $request
     */
    #[Middleware('permission:package-variant-master-create')]
    public function store(StorePackageRequest $request): JsonResponse
    {
        $package = $this->packageService->createPackage(
            PackageData::from($request),
            $request->file('image'),
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Paket berhasil ditambahkan.',
            'data' => [
                'id' => $package->id,
                'category_id' => $package->category_id,
                'name' => $package->name,
                'slug' => $package->slug,
                'description' => $package->description,
                'is_active' => $package->is_active,
                'image_url' => $package->getFirstMediaUrl('package-image'),
            ],
        ], 201);
    }

    /**
     * Update data paket
     * @param UpdatePackageRequest $request
     * @param string $slug
     */
    #[Middleware('permission:package-variant-master-update')]
    public function update(UpdatePackageRequest $request, string $slug): JsonResponse
    {
        $package = $this->packageService->updatePackage(
            $slug,
            PackageData::from($request),
            $request->file('image'),
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Paket berhasil diperbarui.',
            'data' => [
                'id' => $package->id,
                'category_id' => $package->category_id,
                'name' => $package->name,
                'slug' => $package->slug,
                'description' => $package->description,
                'is_active' => $package->is_active,
                'image_url' => $package->getFirstMediaUrl('package-image'),
            ],
        ]);
    }

    /**
     * Hapus data paket
     * @param string $slug
     */
    #[Middleware('permission:package-variant-master-delete')]
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
    #[Middleware('permission:package-variant-master-update')]
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
