<?php

namespace App\Domains\MasterData\Repositories;

use App\Domains\MasterData\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CategoryRepository
{
  /**
   * Blueprint query kategori yang aktif.
   */
  public function queryActive(): Builder
  {
    return Category::where('is_active', true);
  }

  /**
   * Mengambil semua kategori yang aktif.
   */
  public function getActive(): Collection
  {
    return $this->queryActive()->get();
  }

  /**
   * Menghitung total kategori yang aktif.
   */
  public function countActive(): int
  {
    return $this->queryActive()->count();
  }

  /**
   * Mencari kategori berdasarkan Slug.
   * @param string $slug
   */
  public function findBySlug(string $slug): ?Category
  {
    return Category::where('slug', $slug)->first();
  }

  /**
   * Membuat kategori baru.
   * @param array $data
   */
  public function create(array $data): Category
  {
    return Category::create($data);
  }

  /**
   * Memperbarui data kategori.
   * @param Category $category
   * @param array $data
   */
  public function update(Category $category, array $data): Category
  {
    $category->update($data);
    return $category;
  }

  /**
   * Menghapus kategori.
   * @param Category $category
   */
  public function delete(Category $category): ?bool
  {
    return $category->delete();
  }
}
