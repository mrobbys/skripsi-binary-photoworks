<?php

namespace App\Domains\MasterData\Services;

use App\Domains\MasterData\DTOs\CategoryData;
use App\Domains\MasterData\Models\Category;
use Illuminate\Database\Eloquent\Builder;

class CategoryService
{
	/**
	 * Query pencarian kategori
	 * Digunakan di halaman index table data kategori
	 * @param ?string $search
	 */
	public function searchQuery(?string $search): Builder
	{
		$query = Category::query()->orderBy('created_at', 'desc');

		if ($search) {
			$query->where(function ($q) use ($search) {
				$searchTerm = '%' . $search . '%';
				$q->where('name', 'ILIKE', $searchTerm)
					->orWhere('category_code', 'ILIKE', $searchTerm);
			});
		}

		return $query;
	}

	/**
	 * Membuat kategori baru
	 * @param CategoryData $data
	 */
	public function createCategory(CategoryData $data): Category
	{
		return Category::create($data->toArray());
	}

	/**
	 * Memperbarui data kategori
	 * @param string $slug
	 * @param CategoryData $data
	 */
	public function updateCategory(string $slug, CategoryData $data): Category
	{
		$category = Category::where('slug', $slug)->firstOrFail();
		$category->update($data->toArray());
		return $category;
	}

	/**
	 * Menghapus kategori
	 * @param string $slug
	 */
	public function deleteCategory(string $slug): ?bool
	{
		$category = Category::where('slug', $slug)->firstOrFail();

		// cek apakah paket masih ada yang menggunakan kategori tertentu
		if ($category->packages()->exists()) {
			throw new \RuntimeException(
				'Kategori tidak dapat dihapus karena masih memiliki paket terkait.'
			);
		}

		return $category->delete();
	}

	/**
	 * Ubah status aktif kategori dengan toggle
	 * @param string $slug
	 */
	public function toggleCategoryActiveStatus(string $slug): Category
	{
		$category = Category::where('slug', $slug)->firstOrFail();
		$category->update(['is_active' => !$category->is_active]);
		return $category;
	}
}
