<?php

use App\Domains\SystemSettings\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')
  ->prefix('backdoor/system-settings/users')
  ->name('backdoor.system-settings.users.')
  ->group(function () {

    // Halaman index
    Route::get('/', [UserManagementController::class, 'index'])
      ->name('index');

    // JSON endpoint untuk useDatatable
    Route::get('/data', [UserManagementController::class, 'data'])
      ->name('data');

    // Simpan user baru
    Route::post('/', [UserManagementController::class, 'store'])
      ->name('store');

    // Update user yang ada
    Route::put('/{user}', [UserManagementController::class, 'update'])
      ->name('update');

    // Hapus user (hard delete)
    Route::delete('/{user}', [UserManagementController::class, 'destroy'])
      ->name('destroy');

    // Reset password ke Password123
    Route::patch('/{user}/reset-password', [UserManagementController::class, 'resetPassword'])
      ->name('reset-password');
  });
