<?php

namespace App\Domains\MasterData\DTOs;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\LaravelData\Data;

class PackageVariantData extends Data
{
	public function __construct(
		public readonly string $name,
		public readonly int $price,
		public readonly int $duration,
		public readonly bool $is_whatsapp_only = false,
		public readonly bool $is_active = true,
		public readonly array $features = [],
	) {}

	public static function fromRequest(FormRequest $request): self
	{
		return new self(
			name: trim($request->validated('name')),
			price: (int) $request->validated('price'),
			duration: (int) $request->validated('duration'),
			is_whatsapp_only: (bool) $request->validated('is_whatsapp_only'),
			is_active: (bool) $request->validated('is_active'),
			features: $request->validated('features') ?? [],
		);
	}
}
