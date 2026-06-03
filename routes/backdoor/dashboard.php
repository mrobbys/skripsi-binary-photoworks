<?php

use Illuminate\Support\Facades\Route;

Route::get('/backdoor/dashboard', function () {
    return view('backdoor.dashboard.pages.index');
})->middleware(['auth'])->name('backdoor.dashboard');
