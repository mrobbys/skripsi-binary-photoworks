<?php

use App\Domains\User\Http\Controllers\AuthGoogleController;
use App\Domains\User\Http\Controllers\ForgotPasswordController;
use App\Domains\User\Http\Controllers\LoginController;
use App\Domains\User\Http\Controllers\LogoutController;
use App\Domains\User\Http\Controllers\RegisterController;
use App\Domains\User\Http\Controllers\ResetPasswordController;
use Illuminate\Support\Facades\Route;


Route::middleware('guest')->group(function () {
  // login routes
  Route::get('login', [LoginController::class, 'index'])
    ->name('login');
  Route::post('auth/login', [LoginController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('login.store');

  // register routes
  Route::get('register', [RegisterController::class, 'index'])
    ->name('register');
  Route::post('auth/register', [RegisterController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('register.store');

  // auth with google | socialite
  Route::get('auth/google', [AuthGoogleController::class, 'redirectToGoogle'])
    ->name('auth.google');

  // forgot password
  Route::get('forgot-password', [ForgotPasswordController::class, 'index'])
    ->name('forgot.password.index');
  Route::post('auth/forgot-password', [ForgotPasswordController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('forgot.password.email');

  // halaman check email
  Route::get('forgot-password/check-email', [ForgotPasswordController::class, 'show'])
    ->name('forgot.password.check.email');

  // resend email link reset password
  Route::post('auth/forgot-password/resend', [ForgotPasswordController::class, 'resend'])
    ->middleware('throttle:5,1')
    ->name('forgot.password.resend');

  // reset password
  Route::get('reset-password/{token}', [ResetPasswordController::class, 'index'])
    ->name('reset.password.index');
  Route::post('auth/reset-password', [ResetPasswordController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('reset.password.store');
});

// google auth callback tanpa middleware guest
Route::get('auth/google/callback', [AuthGoogleController::class, 'handleGoogleCallback'])
  ->name('auth.google.callback');

// logout
Route::post('auth/logout', [LogoutController::class, 'destroy'])
  ->middleware('auth')
  ->name('logout');
