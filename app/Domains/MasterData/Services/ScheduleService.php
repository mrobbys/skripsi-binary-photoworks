<?php

namespace App\Domains\MasterData\Services;

use App\Domains\MasterData\DTOs\ScheduleData;
use App\Domains\MasterData\Models\Schedule;
use App\Domains\MasterData\Repositories\ScheduleRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ScheduleService
{
  public function __construct(
    protected ScheduleRepository $scheduleRepository,
  ) {}

  /**
   * Perbarui data jadwal
   * @param int $id
   * @param ScheduleData $data
   */
  public function updateSchedule(int $id, ScheduleData $data): Schedule
  {
    $schedule = $this->findOrFail($id);
    return $this->scheduleRepository->update(
      $schedule,
      $data->except('id', 'day', 'day_label')->toArray()
    );
  }

  /**
   * Perbarui status aktif jadwal
   * @param int $id
   */
  public function toggleActiveStatus(int $id): Schedule
  {
    $schedule = $this->findOrFail($id);

    return $this->scheduleRepository->update($schedule, [
      'is_active' => ! $schedule->is_active,
    ]);
  }

  /**
   * Mencari jadwal berdasarkan id
   * @param int $id
   */
  private function findOrFail(int $id): Schedule
  {
    $schedule = $this->scheduleRepository->findById($id);

    if (! $schedule) {
      throw new ModelNotFoundException('Jadwal tidak ditemukan.');
    }

    return $schedule;
  }
}
