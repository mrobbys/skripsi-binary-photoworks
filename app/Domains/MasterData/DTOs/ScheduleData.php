<?php

namespace App\Domains\MasterData\DTOs;

use App\Domains\MasterData\Models\Schedule;
use Illuminate\Foundation\Http\FormRequest;
use Spatie\LaravelData\Data;

class ScheduleData extends Data
{
  public function __construct(
    public readonly string $start_time,
    public readonly string $end_time,
    public readonly bool   $is_active,

    public readonly ?int $id = null,
    public readonly ?string $day = null,
    public readonly ?string $day_label = null
  ) {}

  public static function fromRequest(FormRequest $request): self
  {
    return new self(
      start_time: $request->validated('start_time'),
      end_time: $request->validated('end_time'),
      is_active: (bool) $request->validated('is_active'),
    );
  }

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
