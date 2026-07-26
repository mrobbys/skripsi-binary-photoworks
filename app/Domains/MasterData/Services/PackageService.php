<?php

namespace App\Domains\MasterData\Services;

use App\Domains\MasterData\DTOs\PackageData;
use App\Domains\MasterData\Models\Package;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;

class PackageService
{
	/**
	 * Query pencarian paket
	 * Digunakan di halaman index table data paket
	 * @param ?string $search
	 */
	public function searchQuery(?string $search): Builder
	{
		$query = Package::with([
			'category:id,name',
			'features',
			'variants:package_id,is_active,price',
		])->withCount('variants')
			->orderBy('created_at', 'desc');

		if ($search) {
			$query->where(function ($q) use ($search) {
				$term = '%' . $search . '%';
				$q->where('name', 'ILIKE', $term)
					->orWhereHas('category', fn($cq) => $cq->where('name', 'ILIKE', $term));
			});
		}

		return $query;
	}

	/**
	 * Menambah data paket
	 * @param PackageData $data
	 * @param ?UploadedFile $image
	 */
	public function createPackage(PackageData $data, ?UploadedFile $image = null): Package
	{
		$package = Package::create($data->except('features')->toArray());
		$this->syncFeatures($package, $data->features);

		if ($image) {
			$package
				->addMedia($image)
				->toMediaCollection('package-image');
		}

		return $package->load('features', 'category');
	}

	/**
	 * Memperbarui data paket
	 * @param string $slug
	 * @param PackageData $data
	 * @param ?UploadedFile $image
	 */
	public function updatePackage(string $slug, PackageData $data, ?UploadedFile $image = null): Package
	{
		$package = Package::where('slug', $slug)->firstOrFail();
		$package->update($data->except('features')->toArray());
		$this->syncFeatures($package, $data->features);

		if ($image) {
			$package
				->addMedia($image)
				->toMediaCollection('package-image');
		}

		return $package->load('features', 'category');
	}

	/**
	 * Menghapus data paket
	 * @param string $slug
	 */
	public function deletePackage(string $slug): bool
	{
		$package = Package::where('slug', $slug)->firstOrFail();

		if ($package->variants()->whereHas('bookings')->exists()) {
			throw new \RuntimeException(
				'Paket tidak dapat dihapus karena masih memiliki varian yang terhubung dengan pemesanan.'
			);
		}

		return $package->delete();
	}

	/**
	 * Mengubah status aktif paket
	 * @param string $slug
	 */
	public function toggleActiveStatus(string $slug): Package
	{
		$package = Package::where('slug', $slug)->firstOrFail();
		$package->update(['is_active' => !$package->is_active]);
		return $package;
	}

	/**
	 * Memperbarui data paket / deskripsi / fitur paket
	 * @param Package $package
	 * @param array $featureDescriptions
	 */
	private function syncFeatures(Package $package, array $featureDescriptions): void
	{
		$package->features()->delete();

		$featureData = collect($featureDescriptions)
			->filter(fn(string $desc) => trim($desc) !== '')
			->map(fn(string $desc) => ['description' => trim($desc)])
			->toArray();

		if (!empty($featureData)) {
			$package->features()->createMany($featureData);
		}
	}
}
