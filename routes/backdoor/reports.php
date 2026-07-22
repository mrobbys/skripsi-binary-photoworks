<?php

use App\Domains\BackdoorReports\Http\Controllers\ReportIndexController;
use App\Domains\PdfReports\Http\Controllers\RekapitulasiPendapatanTransaksiController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')
  ->prefix('backdoor/reports')
  ->name('backdoor.reports.')
  ->group(function () {
    Route::get('/', [ReportIndexController::class, 'index'])->name('index');
    Route::get('/rekapitulasi-pendapatan-transaksi', RekapitulasiPendapatanTransaksiController::class)->name('pendapatan.pdf');
  });
