<?php

namespace App\Domains\MasterData\Http\Controllers;

use App\Domains\MasterData\Http\Requests\PackageVariantRequest;
use App\Domains\MasterData\Models\PackageVariant;
use App\Domains\MasterData\Services\PackageVariantService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PackageVariantController extends Controller
{
  public function __construct(
    protected PackageVariantService $variantService,
  ) {}

  public function index(Request $request, string $packageSlug): JsonResponse
  {
    $limit = max(1, min((int) $request->query('limit', 10), 100));

    $variants = PackageVariant::with('features')
      ->whereHas('package', fn($q) => $q->where('slug', $packageSlug))
      ->orderBy('created_at', 'asc')
      ->paginate($limit);

    return response()->json($variants);
  }

  public function store(PackageVariantRequest $request, string $packageSlug): JsonResponse
  {
    $variant = $this->variantService->createVariant($packageSlug, $request->toDto());

    return response()->json([
      'status' => 'success',
      'message' => 'Varian berhasil ditambahkan.',
      'data' => $variant,
    ], 201);
  }

  public function update(PackageVariantRequest $request, string $packageSlug, PackageVariant $variant): JsonResponse
  {
    $variant = $this->variantService->updateVariant($variant, $request->toDto());

    return response()->json([
      'status' => 'success',
      'message' => 'Varian berhasil diperbarui.',
      'data' => $variant,
    ]);
  }

  public function destroy(string $packageSlug, PackageVariant $variant): JsonResponse
  {
    $this->variantService->deleteVariant($variant);

    return response()->json([
      'status' => 'success',
      'message' => 'Varian berhasil dihapus.',
    ]);
  }

  public function toggleActive(string $packageSlug, PackageVariant $variant): JsonResponse
  {
    $updated = $this->variantService->toggleVariantActiveStatus($variant);

    return response()->json([
      'status' => 'success',
      'message' => 'Status varian berhasil diperbarui.',
      'data' => $updated,
    ]);
  }
}
