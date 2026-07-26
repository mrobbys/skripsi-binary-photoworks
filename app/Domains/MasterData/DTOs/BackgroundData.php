<?php

namespace App\Domains\MasterData\DTOs;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\LaravelData\Data;

class BackgroundData extends Data
{
	public function __construct(
		public readonly string $name,
		public readonly ?string $description,
		public readonly bool $is_active = true,
	) {}

	public static function fromRequest(FormRequest $request): self
	{
		return new self(
			name: trim($request->validated('name')),
			description: $request->validated('description') ? trim($request->validated('description')) : null,
			is_active: (bool) $request->validated('is_active'),
		);
	}
}
