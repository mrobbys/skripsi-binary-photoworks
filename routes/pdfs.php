<?php

use App\Domains\PdfReports\Http\Controllers\JadwalOperasionalHarianController;
use App\Domains\PdfReports\Http\Controllers\PaymentReceiptController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
  // report bukti pembayaran / kuitansi
  Route::get('/payments/{booking:booking_code}/receipt', PaymentReceiptController::class)
    ->name('payments.receipt');

  // cetak jadwal hari ini
  Route::get('/session-schedule/daily-report', JadwalOperasionalHarianController::class)
    ->name('session-schedule.daily-report');
});
