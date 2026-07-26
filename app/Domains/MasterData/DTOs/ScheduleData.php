<?php

namespace App\Domains\MasterData\DTOs;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\LaravelData\Data;

class ScheduleData extends Data
{
	public function __construct(
		public readonly string $start_time,
		public readonly string $end_time,
		public readonly bool $is_active,
	) {}

	public static function fromRequest(FormRequest $request): self
	{
		return new self(
			start_time: $request->validated('start_time'),
			end_time: $request->validated('end_time'),
			is_active: (bool) $request->validated('is_active'),
		);
	}
}
