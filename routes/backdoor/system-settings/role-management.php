<?php

use App\Domains\SystemSettings\Http\Controllers\RoleManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')
  ->prefix('backdoor/system-settings/roles')
  ->name('backdoor.system-settings.roles.')
  ->group(function () {

    // Halaman index (view)
    Route::get('/', [RoleManagementController::class, 'index'])
      ->name('index');

    // JSON endpoint untuk useDatatable
    Route::get('/data', [RoleManagementController::class, 'data'])
      ->name('data');

    // Form create (view)
    Route::get('/create', [RoleManagementController::class, 'create'])
      ->name('create');

    // Store role baru
    Route::post('/', [RoleManagementController::class, 'store'])
      ->name('store');

    // Halaman detail (view)
    Route::get('/{role}', [RoleManagementController::class, 'show'])
      ->name('show');

    // Form edit (view)
    Route::get('/{role}/edit', [RoleManagementController::class, 'edit'])
      ->name('edit');

    // Update role
    Route::put('/{role}', [RoleManagementController::class, 'update'])
      ->name('update');

    // Hapus role
    Route::delete('/{role}', [RoleManagementController::class, 'destroy'])
      ->name('destroy');
  });
