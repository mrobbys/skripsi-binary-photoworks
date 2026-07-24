<?php

use App\Domains\Review\Http\Controllers\Frontdoor\ReviewController;
use Illuminate\Support\Facades\Route;

Route::get('/reviews', [ReviewController::class, 'index'])->name('frontdoor.reviews');
Route::get('/data', [ReviewController::class, 'data'])->name('frontdoor.reviews.data');

Route::middleware('auth')->group(function () {
    Route::post('/reviews', [ReviewController::class, 'store'])->name('frontdoor.reviews.store');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('frontdoor.reviews.destroy');
});
