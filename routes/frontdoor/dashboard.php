<?php

use Illuminate\Support\Facades\Route;
use App\Domains\User\Http\Controllers\Frontdoor\DashboardController;

Route::middleware('auth')->group(function () {
  // halaman jadwal saya
  Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('frontdoor.dashboard.index');
  // api ambil daftar booking history
  Route::get('/dashboard/appointments', [DashboardController::class, 'appointments'])
    ->name('frontdoor.dashboard.appointments');
  // action repay midtrans / lunasi pemayaran
  Route::post('/dashboard/repay', [DashboardController::class, 'repay'])
    ->name('frontdoor.dashboard.repay');
  // halaman profil
  Route::get('/dashboard/profil', [DashboardController::class, 'profil'])
    ->name('frontdoor.dashboard.profil');
});
