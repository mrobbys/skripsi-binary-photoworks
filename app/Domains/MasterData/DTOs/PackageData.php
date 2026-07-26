<?php

namespace App\Domains\MasterData\DTOs;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\LaravelData\Data;

class PackageData extends Data
{
	public function __construct(
		public readonly int $category_id,
		public readonly string $name,
		public readonly ?string $description,
		public readonly bool $is_active = true,
		public readonly array $features = [],
	) {}

	public static function fromRequest(FormRequest $request): self
	{
		return new self(
			category_id: (int) $request->validated('category_id'),
			name: trim($request->validated('name')),
			description: trim($request->validated('description')),
			is_active: (bool) $request->validated('is_active'),
			features: $request->validated('features') ?? [],
		);
	}
}
