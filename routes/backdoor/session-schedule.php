<?php

use App\Domains\Booking\Http\Controllers\Backdoor\ManageBookingController;
use App\Domains\Booking\Http\Controllers\Backdoor\SessionScheduleController;
use Illuminate\Support\Facades\Route;

Route::prefix('session-schedule')
  ->name('session-schedule.')
  ->group(function () {
    // halaman daftar jadwal
    Route::get('/list', [SessionScheduleController::class, 'index'])
      ->name('list');
    // data untuk table
    Route::get('/list/data', [SessionScheduleController::class, 'data'])
      ->name('list.data');
    // halaman detail jadwal
    Route::get('/list/{booking:booking_code}/detail', [ManageBookingController::class, 'show'])
      ->name('list.show');

    // kalender sesi
    Route::get('/calendar', [SessionScheduleController::class, 'calendar'])
      ->name('calendar');
    // kalender event
    Route::get('/calendar/events', [SessionScheduleController::class, 'events'])
      ->name('calendar.events');
  });
