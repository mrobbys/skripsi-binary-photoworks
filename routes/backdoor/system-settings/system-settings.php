<?php

use Illuminate\Support\Facades\Route;

Route::prefix('system-settings')
  ->name('system-settings.')
  ->group(function () {
    require __DIR__ . '/user-management.php';
    require __DIR__ . '/role-management.php';
    require __DIR__ . '/activity-logs.php';
  });
