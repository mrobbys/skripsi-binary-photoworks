<?php

use App\Domains\BackdoorReports\Http\Controllers\ReportIndexController;
use App\Domains\PdfReports\Http\Controllers\DataKlienController;
use App\Domains\PdfReports\Http\Controllers\DataPaketController;
use App\Domains\PdfReports\Http\Controllers\JadwalOperasionalHarianController;
use App\Domains\PdfReports\Http\Controllers\RekapitulasiJadwalPemotretanController;
use App\Domains\PdfReports\Http\Controllers\PendapatanTahunanController;
use App\Domains\PdfReports\Http\Controllers\RekapitulasiPemesananController;
use App\Domains\PdfReports\Http\Controllers\RekapitulasiPendapatanTransaksiController;
use App\Domains\PdfReports\Http\Controllers\RekapitulasiPerformaHariController;
use App\Domains\PdfReports\Http\Controllers\RekapitulasiPendapatanAddonController;
use App\Domains\PdfReports\Http\Controllers\RekapitulasiUlasanPelangganController;
use Illuminate\Support\Facades\Route;

Route::prefix('reports')
  ->name('reports.')
  ->group(function () {
    // tampil halaman index / semua pilihan report
    Route::get('/', [ReportIndexController::class, 'index'])->name('index');

    // report rekapitulasi pendapatan transaksi
    Route::get('/rekapitulasi-pendapatan-transaksi', RekapitulasiPendapatanTransaksiController::class)->name('pendapatan.pdf');

    // laporan pendapatan tahunan
    Route::get('/pendapatan-tahunan', PendapatanTahunanController::class)->name('pendapatan-tahunan.pdf');

    // report rekapitulasi pemesanan
    Route::get('/rekapitulasi-pemesanan', RekapitulasiPemesananController::class)->name('pemesanan.pdf');

    // laporan jadwal operasional harian
    Route::get('/jadwal-operasional-harian', JadwalOperasionalHarianController::class)->name('jadwal-harian.pdf');

    // laporan rekapitulasi performa hari
    Route::get('/rekapitulasi-performa-hari', RekapitulasiPerformaHariController::class)->name('performa-hari.pdf');

    // laporan rekapitulasi ulasan pelanggan
    Route::get('/rekapitulasi-ulasan-pelanggan', RekapitulasiUlasanPelangganController::class)->name('ulasan-pelanggan.pdf');

    // laporan data klien
    Route::get('/data-klien', DataKlienController::class)->name('data-klien.pdf');

    // laporan data paket katalog master
    Route::get('/data-paket', DataPaketController::class)->name('data-paket.pdf');

    // laporan rekapitulasi jadwal pemotretan
    Route::get('/rekapitulasi-jadwal-pemotretan', RekapitulasiJadwalPemotretanController::class)->name('jadwal-pemotretan.pdf');

    // laporan rekapitulasi pendapatan add-ons
    Route::get('/rekapitulasi-pendapatan-addon', RekapitulasiPendapatanAddonController::class)->name('pendapatan-addon.pdf');
  });
