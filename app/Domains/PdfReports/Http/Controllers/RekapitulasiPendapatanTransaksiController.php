<?php

namespace App\Domains\PdfReports\Http\Controllers;

use App\Domains\Payment\Enums\PaymentStatus;
use App\Domains\Payment\Models\Payment;
use App\Domains\PdfReports\Http\Requests\DateRangeReportRequest;
use App\Http\Controllers\Controller;
use App\Domains\PdfReports\Traits\HasPdfMetadata;
use App\Support\Formatter;
use Carbon\Carbon;
use Illuminate\Support\Fluent;

use function Spatie\LaravelPdf\Support\pdf;

class RekapitulasiPendapatanTransaksiController extends Controller
{
    use HasPdfMetadata;

  public function __invoke(DateRangeReportRequest $request)
  {
    $startDate = Carbon::parse($request->validated('start_date'))->startOfDay();
    $endDate = Carbon::parse($request->validated('end_date'))->endOfDay();

    /**
     * Ambil data payment yang statusnya SETTLEMENT
     * Dan tanggalnya antara start_date dan end_date (berdasarkan pay_date)
     */
    $payments = Payment::with([
        'booking:id,booking_code,user_id,package_variant_id',
        'booking.user:id,name',
        'booking.packageVariant:id,package_id,name,price',
        'booking.packageVariant.package:id,name',
        'booking.addons:id,name,price',
      ])
      ->select(['id', 'booking_id', 'amount', 'pay_date', 'payment_purpose', 'status'])
      ->where('status', PaymentStatus::SETTLEMENT)
      ->whereNotNull('pay_date')
      ->whereBetween('pay_date', [$startDate, $endDate])
      ->orderBy('pay_date', 'asc')
      ->get();

    $rows = $payments->map(function ($payment, $index) {
      $booking = $payment->booking;
      $variant = $booking?->packageVariant;
      $package = $variant?->package;

      $packageName = $package && $variant ? "{$package->name} - {$variant->name}" : ($package?->name ?? '-');
      $packagePrice = $variant?->price ?? 0;

      // Hitung total penghasilan dari addons
      $addonsTotal = $booking?->addons ? $booking->addons->sum(fn($addonItem) => ($addonItem->pivot->price_at_purchase ?? 0) * ($addonItem->pivot->quantity ?? 1)) : 0;

      $totalPendapatan = $payment->amount ?? 0;

      return new Fluent([
        'no' => $index + 1,
        'pay_date' => Formatter::dateId($payment->pay_date, 'd-m-Y'),
        'booking_code' => $booking?->booking_code ?? '-',
        'client_name' => $booking?->user?->name ?? '-',
        'package_name' => $packageName,
        'package_price' => Formatter::rupiah($packagePrice),
        'addons_total' => Formatter::rupiah($addonsTotal),
        'payment_purpose' => $payment->payment_purpose?->value ?? '-',
        'total_pendapatan' => Formatter::rupiah($totalPendapatan),
        'raw_total' => $totalPendapatan,
      ]);
    });

    $grandTotal = Formatter::rupiah($payments->sum('amount'));
    $filterText = Formatter::dateId($startDate) . ' s/d ' . Formatter::dateId($endDate);

    return pdf()
      ->view('pdfs.rekapitulasi-pendapatan-transaksi', $this->pdfData(compact('rows', 'grandTotal', 'filterText')))
      ->format('a4')
      ->landscape()
      ->margins(10, 10, 10, 10)
      ->name("laporan-rekapitulasi-pendapatan-transaksi-{$startDate->format('d-m-Y')}-sd-{$endDate->format('d-m-Y')}.pdf");
  }
}
