<?php

namespace App\Domains\MasterData\Services;

use App\Domains\MasterData\DTOs\CategoryData;
use App\Domains\MasterData\Models\Category;
use App\Domains\MasterData\Repositories\CategoryRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CategoryService
{
  public function __construct(
    protected CategoryRepository $categoryRepository
  ) {}

  /**
   * Logika bisnis simpan kategori baru (otomatis mengubah kode menjadi HURUF BESAR).
   * @param CategoryData $data
   */
  public function createCategory(CategoryData $data): Category
  {
    return $this->categoryRepository->create($data->toArray());
  }

  /**
   * Logika bisnis perbarui kategori.
   * @param CategoryData $data
   */
  public function updateCategory(string $slug, CategoryData $data): Category
  {
    // cari kategori berdasarkan slug
    $category = $this->findCategoryOrFail($slug);
    return $this->categoryRepository->update($category, $data->toArray());
  }

  /**
   * Logika bisnis hapus kategori.
   * @param string $slug
   */
  public function deleteCategory(string $slug): bool
  {
    // cari kategori berdasarkan slug
    $category = $this->findCategoryOrFail($slug);
    return $this->categoryRepository->delete($category);
  }

  /**
   * Logika toggle status aktif / is_active.
   * @param string $slug
   */
  public function toggleCategoryActiveStatus(string $slug): Category
  {
    // cari kategori berdasarkan slug
    $category = $this->findCategoryOrFail($slug);

    return $this->categoryRepository->update($category, [
      'is_active' => !$category->is_active,
    ]);
  }

  // HELPER METHODS
  /**
   * Mencari kategori berdasarkan Slug.
   * @param string $slug
   */
  private function findCategoryOrFail(string $slug): Category
  {
    $category = $this->categoryRepository->findBySlug($slug);

    if (!$category) {
      throw new ModelNotFoundException("Kategori tidak ditemukan.");
    }

    return $category;
  }
}
