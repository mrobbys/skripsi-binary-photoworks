<?php

use Illuminate\Support\Facades\Route;
use App\Domains\Frontdoor\Http\Controllers\HomeController;
use App\Domains\Frontdoor\Http\Controllers\AboutController;
use App\Domains\Booking\Http\Controllers\Frontdoor\BookingController;
use App\Domains\Review\Http\Controllers\Frontdoor\ReviewController;
use App\Domains\Frontdoor\Http\Controllers\FaqController;
use App\Domains\Frontdoor\Http\Controllers\ContactUsController;

Route::get('/', [HomeController::class, 'index'])->middleware('auth')->name('frontdoor.home');

Route::get('/about', [AboutController::class, 'index'])->name('frontdoor.about');

Route::get('/services', [BookingController::class, 'services'])->name('frontdoor.services');

Route::get('/reviews', [ReviewController::class, 'index'])->name('frontdoor.reviews');

Route::get('/faq', [FaqController::class, 'index'])->name('frontdoor.faq');

Route::get('/contact-us', [ContactUsController::class, 'index'])->name('frontdoor.contact-us');

require __DIR__ . '/booking.php';