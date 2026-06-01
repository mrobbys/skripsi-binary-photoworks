<?php

use App\Domains\Auth\Http\Controllers\LoginController;
use App\Domains\Auth\Http\Controllers\RegisterController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {

  // login routes
  Route::get('login', [LoginController::class, 'index'])->name('login');
  Route::post('login', [LoginController::class, 'store'])->name('login.store');

  // register routes
  Route::get('register', [RegisterController::class, 'index'])->name('register');
  Route::post('register', [RegisterController::class, 'store'])->name('register.store');
});

Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
