<?php

namespace App\Domains\MasterData\Http\Controllers;

use App\Domains\MasterData\DTOs\CategoryData;
use App\Domains\MasterData\Http\Requests\StoreCategoryRequest;
use App\Domains\MasterData\Http\Requests\UpdateCategoryRequest;
use App\Domains\MasterData\Services\CategoryService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\View\View;

#[Middleware('permission:category-master-view', only: ['index', 'data'])]
#[Middleware('permission:category-master-create', only: ['store'])]
#[Middleware('permission:category-master-update', only: ['update', 'toggleActive'])]
#[Middleware('permission:category-master-delete', only: ['destroy'])]
class CategoryController extends Controller
{
	public function __construct(
		protected CategoryService $categoryService,
	) {}

	public function index(): View
	{
		return view('backdoor.data-master.category.index');
	}

	public function data(Request $request): JsonResponse
	{
		$search = $request->query('search');
		$limit = max(1, min((int) $request->query('limit', 10), 100));

		$categories = $this->categoryService->searchQuery($search)->paginate($limit);

		return response()->json([
			'data' => $categories->items(),
			'current_page' => $categories->currentPage(),
			'last_page' => $categories->lastPage(),
			'total' => $categories->total(),
			'active_count' => $this->categoryService->countActive(),
		]);
	}

	public function store(StoreCategoryRequest $request): JsonResponse
	{
		try {
			$category = $this->categoryService->createCategory(CategoryData::fromRequest($request));

			return $this->successResponse('Kategori berhasil ditambahkan', $category, 201);
		} catch (\RuntimeException $e) {
			return $this->errorResponse($e->getMessage(), 422);
		} catch (\Exception $e) {
			return $this->errorResponse('Terjadi kesalahan server');
		}
	}

	public function update(UpdateCategoryRequest $request, string $slug): JsonResponse
	{
		try {
			$category = $this->categoryService->updateCategory($slug, CategoryData::fromRequest($request));

			return $this->successResponse('Kategori berhasil diperbarui', $category);
		} catch (\RuntimeException $e) {
			return $this->errorResponse($e->getMessage(), 422);
		} catch (\Exception $e) {
			return $this->errorResponse('Terjadi kesalahan server');
		}
	}

	public function destroy(string $slug): JsonResponse
	{
		try {
			$this->categoryService->deleteCategory($slug);

			return $this->successResponse('Kategori berhasil dihapus');
		} catch (\RuntimeException $e) {
			return $this->errorResponse($e->getMessage(), 422);
		} catch (\Exception $e) {
			return $this->errorResponse('Terjadi kesalahan server');
		}
	}

	public function toggleActive(string $slug): JsonResponse
	{
		try {
			$category = $this->categoryService->toggleCategoryActiveStatus($slug);
			$activeCount = $this->categoryService->countActive();

			return $this->successResponse(
				'Status aktif kategori berhasil diperbarui',
				$category,
				extra: ['active_count' => $activeCount],
			);
		} catch (\RuntimeException $e) {
			return $this->errorResponse($e->getMessage(), 422);
		} catch (\Exception $e) {
			return $this->errorResponse('Terjadi kesalahan server');
		}
	}
}
