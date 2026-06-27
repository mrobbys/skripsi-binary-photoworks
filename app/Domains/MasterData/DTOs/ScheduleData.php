<?php

namespace App\Domains\MasterData\DTOs;

use Spatie\LaravelData\Data;

class ScheduleData extends Data
{
  public function __construct(
    public readonly string $start_time,
    public readonly string $end_time,
    public readonly bool   $is_active,
  ) {}
}
