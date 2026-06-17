<?php

namespace App\Domains\MasterData\Repositories;

use App\Domains\MasterData\Models\Category;

class CategoryRepository
{
  /**
   * Mencari kategori berdasarkan Slug.
   */
  public function findBySlug(string $slug): ?Category
  {
    return Category::where('slug', $slug)->first();
  }

  /**
   * Membuat kategori baru.
   */
  public function create(array $data): Category
  {
    return Category::create($data);
  }

  /**
   * Memperbarui data kategori.
   */
  public function update(Category $category, array $data): Category
  {
    $category->update($data);
    return $category;
  }

  /**
   * Menghapus kategori.
   */
  public function delete(Category $category): ?bool
  {
    return $category->delete();
  }
}
