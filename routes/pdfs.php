<?php

use App\Http\Controllers\Reports\PaymentReceiptController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
  // report bukti pembayaran
  Route::get('/payments/{payment:order_id}/receipt', PaymentReceiptController::class)
    ->name('payments.receipt');
});
