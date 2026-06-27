<?php

namespace App\Domains\MasterData\Repositories;

use App\Domains\MasterData\Models\Schedule;
use Illuminate\Database\Eloquent\Collection;

class ScheduleRepository
{
  public function getAll(): Collection
  {
    return Schedule::query()->orderBy('day')->get();
  }

  public function findById(int $id): ?Schedule
  {
    return Schedule::find($id);
  }

  public function update(Schedule $schedule, array $data): Schedule
  {
    $schedule->update($data);

    return $schedule;
  }
}
