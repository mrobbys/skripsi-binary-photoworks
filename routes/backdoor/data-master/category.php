<?php

use Illuminate\Support\Facades\Route;

Route::get('/backdoor/data-master/category', function () {
  return view('backdoor.data-master.category.pages.index');
})->middleware(['auth'])->name('backdoor.data-master.category');
