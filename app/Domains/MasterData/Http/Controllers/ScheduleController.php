<?php

namespace App\Domains\MasterData\Http\Controllers;

use App\Domains\MasterData\DTOs\ScheduleData;
use App\Domains\MasterData\DTOs\ScheduleViewData;
use App\Domains\MasterData\Http\Requests\UpdateScheduleRequest;
use App\Domains\MasterData\Models\Schedule;
use App\Domains\MasterData\Services\ScheduleService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\View\View;

#[Middleware('permission:schedule-master-view', only: ['index', 'data'])]
#[Middleware('permission:schedule-master-update', only: ['update', 'toggleActive'])]
class ScheduleController extends Controller
{
	public function __construct(
		protected ScheduleService $scheduleService,
	) {}

	public function index(): View
	{
		return view('backdoor.data-master.schedule.index');
	}

	public function data(): JsonResponse
	{
		$schedules = Schedule::query()->orderBy('day')->get();
		$items = $schedules->map(fn(Schedule $s) => ScheduleViewData::fromModel($s));

		return response()->json(['data' => $items]);
	}

	public function update(UpdateScheduleRequest $request, Schedule $schedule): JsonResponse
	{
		try {
			$updated = $this->scheduleService->updateSchedule(
				$schedule->id,
				ScheduleData::fromRequest($request)
			);

			return $this->successResponse(
				'Jadwal berhasil diperbarui.',
				ScheduleViewData::fromModel($updated)
			);
		} catch (\Exception $e) {
			return $this->errorResponse('Terjadi kesalahan server');
		}
	}

	public function toggleActive(Schedule $schedule): JsonResponse
	{
		try {
			$this->scheduleService->toggleActiveStatus($schedule->id);

			return $this->successResponse('Status jadwal berhasil diperbarui.');
		} catch (\Exception $e) {
			return $this->errorResponse('Terjadi kesalahan server');
		}
	}
}
