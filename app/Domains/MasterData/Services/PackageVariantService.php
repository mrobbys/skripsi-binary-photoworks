<?php

namespace App\Domains\MasterData\Services;

use App\Domains\MasterData\DTOs\PackageVariantData;
use App\Domains\MasterData\Models\Package;
use App\Domains\MasterData\Models\PackageVariant;

class PackageVariantService
{
	/**
	 * Tambah data variant baru
	 * @param string $packageSlug
	 * @param PackageVariantData $data
	 */
	public function createVariant(string $packageSlug, PackageVariantData $data): PackageVariant
	{
		$package = Package::where('slug', $packageSlug)->firstOrFail();
		$variant = $package->variants()->create($data->except('features')->toArray());
		$this->syncFeatures($variant, $data->features);

		return $variant->load('features');
	}

	/**
	 * Update data variant
	 * @param PackageVariant $variant
	 * @param PackageVariantData $data
	 */
	public function updateVariant(PackageVariant $variant, PackageVariantData $data): PackageVariant
	{
		$variant->update($data->except('features')->toArray());
		$this->syncFeatures($variant, $data->features);

		return $variant->load('features');
	}

	/**
	 * Hapus data variant
	 * @param PackageVariant $variant
	 */
	public function deleteVariant(PackageVariant $variant): bool
	{
		if ($variant->bookings()->exists()) {
			throw new \RuntimeException(
				'Varian tidak dapat dihapus karena masih terhubung dengan data pemesanan.'
			);
		}

		return $variant->delete();
	}

	/**
	 * Toggle variant active status
	 * @param PackageVariant $variant
	 */
	public function toggleVariantActiveStatus(PackageVariant $variant): PackageVariant
	{
		$variant->update(['is_active' => !$variant->is_active]);
		return $variant;
	}

	/**
	 * @param PackageVariant $variant
	 * @param array $featureDescriptions
	 */
	private function syncFeatures(PackageVariant $variant, array $featureDescriptions): void
	{
		$variant->features()->delete();

		$featureData = collect($featureDescriptions)
			->filter(fn(string $desc) => trim($desc) !== '')
			->map(fn(string $desc) => ['description' => trim($desc)])
			->toArray();

		if (!empty($featureData)) {
			$variant->features()->createMany($featureData);
		}
	}
}
