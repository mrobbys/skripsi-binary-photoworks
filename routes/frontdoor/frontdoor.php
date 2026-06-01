<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
  return view('frontdoor.home.pages.index');
})->middleware('auth')->name('frontdoor.home');
