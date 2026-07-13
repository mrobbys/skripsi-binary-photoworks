<?php

use Illuminate\Support\Facades\Route;
use App\Domains\User\Http\Controllers\Frontdoor\DashboardController;
use App\Domains\User\Http\Controllers\Frontdoor\ProfileController;

Route::middleware('auth')
    ->prefix('/dashboard')
    ->name('frontdoor.dashboard.')
    ->group(function () {
        // halaman jadwal saya
        Route::get('/', [DashboardController::class, 'index'])
            ->name('index');
        // api ambil daftar booking history
        Route::get('/appointments', [DashboardController::class, 'appointments'])
            ->name('appointments');
        // action repay midtrans / lunasi pemayaran
        Route::post('/repay', [DashboardController::class, 'repay'])
            ->name('repay');
        // cancel booking
        Route::post('/cancel', [DashboardController::class, 'cancel'])
            ->name('cancel');
        // reschedule booking
        Route::post('/reschedule', [DashboardController::class, 'reschedule'])
            ->name('reschedule');
        // halaman profil
        Route::get('/profile', [ProfileController::class, 'index'])
            ->name('profile');
        // update data profil
        Route::patch('/profile', [ProfileController::class, 'update'])
            ->name('profile.update');
        // ganti password
        Route::patch('/profile/password', [ProfileController::class, 'changePassword'])
            ->name('profile.password');
    });
