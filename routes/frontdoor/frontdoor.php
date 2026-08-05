<?php

use App\Domains\Frontdoor\Http\Controllers\AboutController;
use App\Domains\Frontdoor\Http\Controllers\ContactUsController;
use App\Domains\Frontdoor\Http\Controllers\FaqController;
use App\Domains\Frontdoor\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('frontdoor.home');

Route::get('/about', [AboutController::class, 'index'])->name('frontdoor.about');

require __DIR__ . '/booking.php';

require __DIR__ . '/review.php';

Route::get('/faq', [FaqController::class, 'index'])->name('frontdoor.faq');

Route::get('/contact-us', [ContactUsController::class, 'index'])->name('frontdoor.contact-us');
Route::post('/contact-us', [ContactUsController::class, 'store'])->name('contact.store')->middleware('throttle:3,1');

require __DIR__ . '/dashboard.php';
