<?php

namespace App\Domains\MasterData\Http\Controllers;

use App\Domains\MasterData\DTOs\CategoryData;
use App\Domains\MasterData\Http\Requests\StoreCategoryRequest;
use App\Domains\MasterData\Http\Requests\UpdateCategoryRequest;
use App\Domains\MasterData\Services\CategoryService;
use App\Domains\MasterData\Repositories\CategoryRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
  public function __construct(
    protected CategoryService $categoryService,
    protected CategoryRepository $categoryRepository
  ) {}

  /**
   * Menampilkan daftar kategori.
   * @param Request $request
   */
  public function index(Request $request): View|JsonResponse
  {
    // hitung jumlah kategori yang aktif
    $activeCount = $this->categoryRepository->countActive();

    // request json / ajax
    if ($request->wantsJson()) {
      $search = $request->query('search');
      $limit = max(1, min((int) $request->query('limit', 10), 100));

      $categories = $this->categoryRepository->searchQuery($search)->paginate($limit);

      return response()->json([
        'data' => $categories->items(),
        'current_page' => $categories->currentPage(),
        'last_page' => $categories->lastPage(),
        'total' => $categories->total(),
        'active_count' => $activeCount,
      ]);
    }

    return view('backdoor.data-master.category.index', [
      'activeCount' => $activeCount
    ]);
  }

  /**
   * Simpan kategori baru.
   * @param StoreCategoryRequest $request
   */
  public function store(StoreCategoryRequest $request): JsonResponse
  {
    $category = $this->categoryService->createCategory(CategoryData::from($request));

    return response()->json([
      'status' => 'success',
      'message' => 'Kategori berhasil ditambahkan',
      'data' => $category,
    ], 201);
  }

  /**
   * Update kategori berdasarkan slug.
   * @param UpdateCategoryRequest $request
   * @param string $slug
   */
  public function update(UpdateCategoryRequest $request, string $slug): JsonResponse
  {
    $category = $this->categoryService->updateCategory($slug, CategoryData::from($request));

    return response()->json([
      'status' => 'success',
      'message' => 'Kategori berhasil diperbarui',
      'data' => $category,
    ]);
  }

  /**
   * Hapus kategori.
   * @param string $slug
   */
  public function destroy(string $slug): JsonResponse
  {
    $this->categoryService->deleteCategory($slug);

    return response()->json([
      'status' => 'success',
      'message' => 'Kategori berhasil dihapus',
    ]);
  }

  /**
   * Ubah status aktif kategori dengan toggle.
   * @param string $slug
   */
  public function toggleActive(string $slug): JsonResponse
  {
    $category = $this->categoryService->toggleCategoryActiveStatus($slug);
    $activeCount = $this->categoryRepository->countActive();

    return response()->json([
      'status' => 'success',
      'message' => 'Status aktif kategori berhasil diperbarui',
      'data' => $category,
      'active_count' => $activeCount,
    ]);
  }
}
