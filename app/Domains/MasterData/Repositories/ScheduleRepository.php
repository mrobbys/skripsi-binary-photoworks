<?php

namespace App\Domains\MasterData\Repositories;

use App\Domains\MasterData\Models\Schedule;
use Illuminate\Database\Eloquent\Collection;

class ScheduleRepository
{
  /**
   * Ambil semua jadwal, urutkan berdasarkan hari
   */
  public function getAll(): Collection
  {
    return Schedule::query()->orderBy('day')->get();
  }

  /**
   * Ambil jadwal berdasarkan id
   * @param int $id
   */
  public function findById(int $id): ?Schedule
  {
    return Schedule::find($id);
  }

  /**
   * Perbarui data jadwal
   * @param Schedule $schedule
   * @param array $data
   */
  public function update(Schedule $schedule, array $data): Schedule
  {
    $schedule->update($data);
    return $schedule;
  }
}
