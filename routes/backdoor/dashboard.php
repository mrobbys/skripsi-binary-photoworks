<?php

use Illuminate\Support\Facades\Route;

Route::get('/backdoor/dashboard', function () {
    return view('backdoor.dashboard.index');
})->middleware(['auth'])->name('backdoor.dashboard');
