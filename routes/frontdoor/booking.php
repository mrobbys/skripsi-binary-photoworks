<?php

use Illuminate\Support\Facades\Route;
use App\Domains\Booking\Http\Controllers\Frontdoor\BookingController;

Route::get('/booking', [BookingController::class, 'index'])->name('frontdoor.booking.index');