<?php

namespace App\Domains\MasterData\Services;

use App\Domains\MasterData\DTOs\ScheduleData;
use App\Domains\MasterData\Models\Schedule;

class ScheduleService
{
  /**
   * Perbarui data jadwal
   * @param int $id
   * @param ScheduleData $data
   */
  public function updateSchedule(int $id, ScheduleData $data): Schedule
  {
    $schedule = Schedule::findOrFail($id);
    $schedule->update($data->toArray());
    return $schedule;
  }

  /**
   * Perbarui status aktif jadwal
   * @param int $id
   */
  public function toggleActiveStatus(int $id): Schedule
  {
    $schedule = Schedule::findOrFail($id);
    $schedule->update(['is_active' => !$schedule->is_active]);
    return $schedule;
  }
}
