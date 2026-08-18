<?php

use App\Domains\Booking\Http\Controllers\Backdoor\ManageBookingController;
use Illuminate\Support\Facades\Route;

Route::prefix('booking-management')
    ->name('booking-management.')
    ->group(function () {
        // tampil semua data booking
        Route::get('/', [ManageBookingController::class, 'index'])->name('index');
        // ambil data datatable
        Route::get('/data', [ManageBookingController::class, 'data'])->name('data');
        // halaman create booking (manual)
        Route::get('/create', [ManageBookingController::class, 'create'])->name('create');
        // submit create
        Route::post('/', [ManageBookingController::class, 'store'])->name('store');
        // halaman detail booking
        Route::get('/{booking:booking_code}', [ManageBookingController::class, 'show'])->name('show');
        // ambil data detail booking (JSON)
        Route::get('/{booking:booking_code}/show-data', [ManageBookingController::class, 'showData'])->name('show-data');
        // aksi lunasi pembayaran
        Route::patch('/{booking:booking_code}/settle', [ManageBookingController::class, 'settle'])->name('settle');
        // aksi kirim link gdrive
        Route::patch('/{booking:booking_code}/gdrive', [ManageBookingController::class, 'updateGdrive'])->name('gdrive');
        // cancel booking
        Route::patch('/{booking:booking_code}', [ManageBookingController::class, 'cancel'])->name('cancel');
        // upsell addon
        Route::post('/{booking:booking_code}/addons', [ManageBookingController::class, 'upsellAddon'])->name('addons.upsell');
        // remove addon di halaman detail
        Route::delete('/{booking:booking_code}/addons/{addon}', [ManageBookingController::class, 'removeAddon'])->name('addons.remove');
        // aksi catat refund kelebihan bayar
        Route::post('/{booking:booking_code}/refund', [ManageBookingController::class, 'refund'])->name('refund');
    });
