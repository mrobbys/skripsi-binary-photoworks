<?php

namespace App\Domains\MasterData\Http\Controllers;

use App\Domains\MasterData\Http\Requests\StorePackageRequest;
use App\Domains\MasterData\Http\Requests\UpdatePackageRequest;
use App\Domains\MasterData\Models\Category;
use App\Domains\MasterData\Models\Package;
use App\Domains\MasterData\Models\PackageVariant;
use App\Domains\MasterData\Services\PackageService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function __construct(
        protected PackageService $packageService,
    ) {}

    // TODO: pindahkan beberapa query ke repository
    
    public function index(Request $request): View|JsonResponse
    {
        $totalPackages = Package::count();
        $totalActivePackages = Package::where('is_active', true)->count();
        $totalActiveVariants = PackageVariant::where('is_active', true)->count();

        if ($request->wantsJson()) {
            $search = $request->query('search');
            $limit = $request->query('limit', 10);

            $query = Package::with(['category', 'variants'])
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

        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'category_code', 'name']);
            
        return view('backdoor.data-master.package.index', [
            'totalPackages' => $totalPackages,
            'totalActivePackages' => $totalActivePackages,
            'totalActiveVariants' => $totalActiveVariants,
            'categories' => $categories,
        ]);
    }

    public function show(string $slug): View
    {
        $package = Package::with(['category', 'features', 'variants.features'])
            ->where('slug', $slug)
            ->firstOrFail();

        return view('backdoor.data-master.package.show', compact('package'));
    }

    public function store(StorePackageRequest $request): JsonResponse
    {
        $package = $this->packageService->createPackage($request->toDto());

        return response()->json([
            'status' => 'success',
            'message' => 'Paket berhasil ditambahkan.',
            'data' => $package,
        ], 201);
    }

    public function update(UpdatePackageRequest $request, string $slug): JsonResponse
    {
        $package = $this->packageService->updatePackage($slug, $request->toDto());

        return response()->json([
            'status' => 'success',
            'message' => 'Paket berhasil diperbarui.',
            'data' => $package,
        ]);
    }

    public function destroy(string $slug): JsonResponse
    {
        $this->packageService->deletePackage($slug);

        return response()->json([
            'status' => 'success',
            'message' => 'Paket berhasil dihapus.',
        ]);
    }

    public function toggleActive(string $slug): JsonResponse
    {
        $package = $this->packageService->toggleActiveStatus($slug);
        $totalActivePackages = Package::where('is_active', true)->count();
        $totalActiveVariants = PackageVariant::where('is_active', true)->count();

        return response()->json([
            'status' => 'success',
            'message' => 'Status paket berhasil diperbarui.',
            'data' => $package,
            'total_active_packages' => $totalActivePackages,
            'total_active_variants' => $totalActiveVariants,
        ]);
    }
}
