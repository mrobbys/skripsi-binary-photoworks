<?php

namespace App\Domains\MasterData\Http\Controllers;

use App\Domains\MasterData\Models\Category;
use App\Domains\MasterData\Http\Requests\StoreCategoryRequest;
use App\Domains\MasterData\Http\Requests\UpdateCategoryRequest;
use App\Domains\MasterData\Services\CategoryService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
  public function __construct(
    protected CategoryService $categoryService
  ) {}

  /**
   * Menampilkan daftar kategori.
   */
  public function index(Request $request): View|JsonResponse
  {
    // hitung jumlah kategori yang aktif
    $activeCount = Category::where('is_active', true)->count();

    // request json / ajax
    if ($request->wantsJson()) {
      $search = $request->query('search');
      $limit = $request->query('limit', 10);

      // ambil data kategori urutkan dari terbaru
      $query = Category::query()->orderBy('created_at', 'desc');

      // jika ada query pencarian
      // pencarian berdasarkan name dan category_code
      if ($search) {
        $query->where(function ($q) use ($search) {
          $searchTerm = '%' . strtolower($search) . '%';
          $q->whereRaw('LOWER(name) LIKE ?', [$searchTerm])
            ->orWhereRaw('LOWER(category_code) LIKE ?', [$searchTerm]);
        });
      }

      $categories = $query->paginate($limit);

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
   */
  public function store(StoreCategoryRequest $request): JsonResponse
  {
    $category = $this->categoryService->createCategory($request->toDto());

    return response()->json([
      'status' => 'success',
      'message' => 'Kategori berhasil ditambahkan',
      'data' => $category,
    ], 201);
  }

  /**
   * Update kategori berdasarkan slug.
   */
  public function update(UpdateCategoryRequest $request, string $slug): JsonResponse
  {
    $category = $this->categoryService->updateCategory($slug, $request->toDto());

    return response()->json([
      'status' => 'success',
      'message' => 'Kategori berhasil diperbarui',
      'data' => $category,
    ]);
  }

  /**
   * Hapus kategori.
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
   */
  public function toggleActive(string $slug): JsonResponse
  {
    $category = $this->categoryService->toggleCategoryActiveStatus($slug);
    $activeCount = Category::where('is_active', true)->count();

    return response()->json([
      'status' => 'success',
      'message' => 'Status aktif kategori berhasil diperbarui',
      'data' => $category,
      'active_count' => $activeCount,
    ]);
  }
}
