<?php

use App\Domains\Auth\Http\Controllers\LoginController;
use App\Domains\Auth\Http\Controllers\RegisterController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {

  // login routes
  Route::get('login', [LoginController::class, 'index'])->name('login');
  Route::post('auth/login', [LoginController::class, 'store'])->name('login.store');

  // register routes
  Route::get('register', [RegisterController::class, 'index'])->name('register');
  Route::post('auth/register', [RegisterController::class, 'store'])->name('register.store');

  // login with google | socialite
  Route::get('auth/google', [LoginController::class, 'redirectToGoogle'])->name('login.google');
  Route::get('auth/google/callback', [LoginController::class, 'handleGoogleCallback'])->name('login.google.callback');
});

Route::post('auth/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');
