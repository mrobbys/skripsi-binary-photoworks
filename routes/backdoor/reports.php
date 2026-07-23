<?php

use App\Domains\BackdoorReports\Http\Controllers\ReportIndexController;
use App\Domains\PdfReports\Http\Controllers\RekapitulasiPemesananController;
use App\Domains\PdfReports\Http\Controllers\RekapitulasiPendapatanTransaksiController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')
  ->prefix('backdoor/reports')
  ->name('backdoor.reports.')
  ->group(function () {
    // tampil halaman index / semua pilihan report
    Route::get('/', [ReportIndexController::class, 'index'])->name('index');

    // report rekapitulasi pendapatan transaksi
    Route::get('/rekapitulasi-pendapatan-transaksi', RekapitulasiPendapatanTransaksiController::class)->name('pendapatan.pdf');

    // report rekapitulasi pemesanan
    Route::get('/rekapitulasi-pemesanan', RekapitulasiPemesananController::class)->name('pemesanan.pdf');
  });
