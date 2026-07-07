<?php

use App\Domains\Booking\Http\Controllers\Frontdoor\BookingController;
use App\Domains\Booking\Http\Controllers\Frontdoor\WebhookController;
use Illuminate\Support\Facades\Route;

// halaman services, tampil semua data paket
Route::get('/services', [BookingController::class, 'services'])
    ->name('frontdoor.services.index');

Route::middleware(['auth'])
    ->prefix('services/booking')
    ->name('frontdoor.booking.')
    ->group(function () {
        // halaman flow booking paket / multi step form
        Route::get('/{package:slug}', [BookingController::class, 'flow'])->name('flow');
        // ambil slot waktu yang tersedia
        Route::get('/api/slots', [BookingController::class, 'getAvailableSlots'])->name('api.slots');
        // proses checkout
        Route::post('/checkout', [BookingController::class, 'checkout'])->name('checkout');
        // halaman checkout berhasil
        Route::get('/success/{bookingCode}', [BookingController::class, 'success'])->name('success');
    });

// Webhook Midtrans — bebas auth & CSRF
Route::post('/api/payments/webhook', [WebhookController::class, 'handle'])
    ->name('payments.webhook');
