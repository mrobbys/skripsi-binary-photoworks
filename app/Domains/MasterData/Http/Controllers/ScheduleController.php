<?php

namespace App\Domains\MasterData\Http\Controllers;

use App\Domains\MasterData\DTOs\ScheduleData;
use App\Domains\MasterData\Http\Requests\UpdateScheduleRequest;
use App\Domains\MasterData\Models\Schedule;
use App\Domains\MasterData\Repositories\ScheduleRepository;
use App\Domains\MasterData\Services\ScheduleService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ScheduleController extends Controller
{
  public function __construct(
    protected ScheduleService    $scheduleService,
    protected ScheduleRepository $scheduleRepository,
  ) {}

  /**
   * Tampilkan data jadwal
   */
  public function index(): View|JsonResponse
  {
    if (request()->wantsJson()) {
      $schedules = $this->scheduleRepository->getAll();
      $items = ScheduleData::collect($schedules);

      return response()->json([
        'data'         => $items,
        'current_page' => 1,
        'last_page'    => 1,
        'total'        => $items->count(),
      ]);
    }

    return view('backdoor.data-master.schedule.index');
  }

  /**
   * Perbarui data jadwal
   * @param UpdateScheduleRequest $request
   * @param Schedule $schedule
   */
  public function update(UpdateScheduleRequest $request, Schedule $schedule): JsonResponse
  {
    $updated = $this->scheduleService->updateSchedule($schedule->id, ScheduleData::fromRequest($request));

    return response()->json([
      'status'  => 'success',
      'message' => 'Jadwal berhasil diperbarui.',
      'data'    => [
        'id'         => $updated->id,
        'start_time' => $updated->start_time?->format('H:i'),
        'end_time'   => $updated->end_time?->format('H:i'),
        'is_active'  => $updated->is_active,
      ],
    ]);
  }

  /**
   * Perbarui status aktif jadwal
   * @param Schedule $schedule
   */
  public function toggleActive(Schedule $schedule): JsonResponse
  {
    $updated = $this->scheduleService->toggleActiveStatus($schedule->id);

    return response()->json([
      'status'  => 'success',
      'message' => 'Status jadwal berhasil diperbarui.',
      'data'    => [
        'id'        => $updated->id,
        'is_active' => $updated->is_active,
      ],
    ]);
  }
}
