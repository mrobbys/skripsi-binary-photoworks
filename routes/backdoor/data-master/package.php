<?php

use App\Domains\MasterData\Http\Controllers\PackageController;
use App\Domains\MasterData\Http\Controllers\PackageVariantController;
use Illuminate\Support\Facades\Route;

Route::prefix('package')
	->name('package.')
	->group(function () {
		// Package Routes
		Route::get('/', [PackageController::class, 'index'])->name('index');
		Route::get('/data', [PackageController::class, 'data'])->name('data');
		Route::post('/', [PackageController::class, 'store'])->name('store');
		Route::get('/{package:slug}/info', [PackageController::class, 'showInfo'])->name('info');
		Route::get('/{package:slug}', [PackageController::class, 'show'])->name('show');
		Route::put('/{package:slug}', [PackageController::class, 'update'])->name('update');
		Route::delete('/{package:slug}', [PackageController::class, 'destroy'])->name('destroy');
		Route::patch('/{package:slug}/toggle', [PackageController::class, 'toggleActive'])->name('toggle');

		// Variant Routes
		Route::get('/{package:slug}/variants', [PackageVariantController::class, 'index'])->name('variants.index');
		Route::post('/{package:slug}/variants', [PackageVariantController::class, 'store'])->name('variants.store');
		Route::put('/{package:slug}/variants/{variant}', [PackageVariantController::class, 'update'])->name('variants.update');
		Route::delete('/{package:slug}/variants/{variant}', [PackageVariantController::class, 'destroy'])->name('variants.destroy');
		Route::patch('/{package:slug}/variants/{variant}/toggle', [PackageVariantController::class, 'toggleActive'])->name('variants.toggle');
	});
