<?php

use App\Domains\User\Http\Controllers\Backdoor\ClientDataController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')
  ->prefix('backdoor/client-data')
  ->name('backdoor.client-data.')
  ->group(function () {
    // halaman daftar klien
    Route::get('/', [ClientDataController::class, 'index'])
      ->name('index');
    // json data untuk table index
    Route::get('/data', [ClientDataController::class, 'data'])
      ->name('data');
    // halaman detail klien
    Route::get('/{user:uuid}', [ClientDataController::class, 'show'])
      ->name('show');
    // json data untuk table show
    Route::get('/{user:uuid}/bookings', [ClientDataController::class, 'bookings'])
      ->name('bookings');
  });
