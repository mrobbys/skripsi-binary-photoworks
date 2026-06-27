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

  public function updateSchedule(int $id, ScheduleData $data): Schedule
  {
    $schedule = $this->findOrFail($id);

    return $this->scheduleRepository->update($schedule, [
      'start_time' => $data->start_time,
      'end_time'   => $data->end_time,
      'is_active'  => $data->is_active,
    ]);
  }

  public function toggleActiveStatus(int $id): Schedule
  {
    $schedule = $this->findOrFail($id);

    return $this->scheduleRepository->update($schedule, [
      'is_active' => ! $schedule->is_active,
    ]);
  }

  private function findOrFail(int $id): Schedule
  {
    $schedule = $this->scheduleRepository->findById($id);

    if (! $schedule) {
      throw new ModelNotFoundException('Jadwal tidak ditemukan.');
    }

    return $schedule;
  }
}
