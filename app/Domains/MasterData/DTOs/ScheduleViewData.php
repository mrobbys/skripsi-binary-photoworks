<?php

namespace App\Domains\MasterData\DTOs;

use App\Domains\MasterData\Models\Schedule;
use Spatie\LaravelData\Data;

class ScheduleViewData extends Data
{
	public function __construct(
		public readonly int $id,
		public readonly string $day,
		public readonly string $day_label,
		public readonly string $start_time,
		public readonly string $end_time,
		public readonly bool $is_active,
	) {}

	public static function fromModel(Schedule $schedule): self
	{
		return new self(
			id: $schedule->id,
			day: $schedule->day->value,
			day_label: $schedule->day->label(),
			start_time: $schedule->start_time?->format('H:i'),
			end_time: $schedule->end_time?->format('H:i'),
			is_active: $schedule->is_active
		);
	}
}
