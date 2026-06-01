<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
  return view('frontdoor.home.pages.index');
})->name('frontdoor.home');
