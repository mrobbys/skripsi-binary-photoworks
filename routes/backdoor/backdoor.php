<?php

use Illuminate\Support\Facades\Route;

// Middleware pastikan sudah login dan role bukan user
Route::middleware(['auth', 'not-user'])
  ->prefix('backdoor')
  ->name('backdoor.')
  ->group(function () {
    require __DIR__ . '/dashboard.php';
    require __DIR__ . '/session-schedule.php';
    require __DIR__ . '/booking-management.php';
    require __DIR__ . '/client-data.php';
    require __DIR__ . '/data-master/data-master.php';
    require __DIR__ . '/client-reviews.php';
    require __DIR__ . '/reports.php';
    require __DIR__ . '/system-settings/system-settings.php';
  });