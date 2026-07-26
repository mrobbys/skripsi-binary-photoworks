<?php

use Illuminate\Support\Facades\Route;

Route::prefix('data-master')
  ->name('data-master.')
  ->group(function () {
    require __DIR__ . '/category.php';
    require __DIR__ . '/package.php';
    require __DIR__ . '/background.php';
    require __DIR__ . '/addon.php';
    require __DIR__ . '/schedule.php';
  });
