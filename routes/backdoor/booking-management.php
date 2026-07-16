<?php

use App\Domains\Booking\Http\Controllers\Backdoor\ManageBookingController;

Route::middleware('auth')
    ->prefix('backdoor/booking-management')
    ->name('backdoor.booking-management.')
    ->group(function () {
        // tampil semua data booking
        Route::get('/', [ManageBookingController::class, 'index'])->name('index');
        // halaman create booking (manual)
        Route::get('/create', [ManageBookingController::class, 'create'])->name('create');
        // endpoint search users
        Route::get('/users/search', [ManageBookingController::class, 'searchUsers'])->name('users.search');
        // submit create
        Route::post('/', [ManageBookingController::class, 'store'])->name('store');
        // halaman detail booking
        Route::get('/{booking:booking_code}', [ManageBookingController::class, 'show'])->name('show');
        // aksi lunasi pembayaran
        Route::patch('/{booking:booking_code}/settle', [ManageBookingController::class, 'settle'])->name('settle');
        // aksi kirim link gdrive
        Route::patch('/{booking:booking_code}/gdrive', [ManageBookingController::class, 'updateGdrive'])->name('gdrive');
        // cancel booking
        Route::patch('/{booking:booking_code}', [ManageBookingController::class, 'cancel'])->name('cancel');
        // upsell addon
        Route::post('/{booking:booking_code}/addons', [ManageBookingController::class, 'upsellAddon'])->name('addons.upsell');
    });
