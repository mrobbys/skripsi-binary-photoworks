<?php

use App\Domains\Auth\Http\Controllers\AuthGoogleController;
use App\Domains\Auth\Http\Controllers\LoginController;
use App\Domains\Auth\Http\Controllers\LogoutController;
use App\Domains\Auth\Http\Controllers\RegisterController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
  // login routes
  Route::get('login', [LoginController::class, 'index'])
    ->name('login');
  Route::post('auth/login', [LoginController::class, 'store'])
    ->name('login.store');

  // register routes
  Route::get('register', [RegisterController::class, 'index'])
    ->name('register');
  Route::post('auth/register', [RegisterController::class, 'store'])
    ->middleware('throttle: 5, 300')
    ->name('register.store');

  // auth with google | socialite
  Route::get('auth/google', [AuthGoogleController::class, 'redirectToGoogle'])
    ->name('auth.google');
  Route::get('auth/google/callback', [AuthGoogleController::class, 'handleGoogleCallback'])
    ->name('auth.google.callback');
});

// logout
Route::post('auth/logout', [LogoutController::class, 'destroy'])
  ->middleware('auth')
  ->name('logout');
