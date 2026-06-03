<?php

use App\Domains\Auth\Http\Controllers\AuthGoogleController;
use App\Domains\Auth\Http\Controllers\ForgotPasswordController;
use App\Domains\Auth\Http\Controllers\LoginController;
use App\Domains\Auth\Http\Controllers\LogoutController;
use App\Domains\Auth\Http\Controllers\RegisterController;
use App\Domains\Auth\Http\Controllers\ResetPasswordController;
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
    ->middleware('throttle:5,300')
    ->name('register.store');

  // auth with google | socialite
  Route::get('auth/google', [AuthGoogleController::class, 'redirectToGoogle'])
    ->name('auth.google');
  Route::get('auth/google/callback', [AuthGoogleController::class, 'handleGoogleCallback'])
    ->name('auth.google.callback');

  // forgot password
  Route::get('forgot-password', [ForgotPasswordController::class, 'index'])
    ->name('forgot.password.index');
  Route::post('auth/forgot-password', [ForgotPasswordController::class, 'store'])
    ->middleware('throttle:5,300')
    ->name('forgot.password.email');

  // halaman check email
  Route::get('forgot-password/check-email', [ForgotPasswordController::class, 'show'])
    ->name('forgot.password.check.email');

  // resend email link reset password
  Route::post('auth/forgot-password/resend', [ForgotPasswordController::class, 'resend'])
    ->middleware('throttle:5,300')
    ->name('forgot.password.resend');

  // reset password
  Route::get('reset-password/{token}', [ResetPasswordController::class, 'index'])
    ->name('reset.password.index');
  Route::post('auth/reset-password', [ResetPasswordController::class, 'store'])
    ->middleware('throttle:5,300')
    ->name('reset.password.store');
});

// logout
Route::post('auth/logout', [LogoutController::class, 'destroy'])
  ->middleware('auth')
  ->name('logout');
