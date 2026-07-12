<?php

namespace App\Domains\MasterData\Repositories;

use App\Domains\MasterData\Models\Schedule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

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
   * Blueprint query schedule yang aktif
   */
  public function queryActive(): Builder
  {
    return Schedule::where('is_active', true);
  }

  /**
   * Ambil semua jadwal yang aktif
   */
  public function getActive(): Collection
  {
    return $this->queryActive()->get();
  }

  /**
   * Ambil data schedule hanya 'day' saja dan buat jadi sebuah array
   */
  public function getDays(): array
  {
    return $this->queryActive()->pluck('day')->toArray();
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
