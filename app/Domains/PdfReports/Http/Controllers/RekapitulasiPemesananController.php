<?php

namespace App\Domains\PdfReports\Http\Controllers;

use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Models\Booking;
use App\Domains\PdfReports\Http\Requests\DateRangeReportRequest;
use App\Http\Controllers\Controller;
use App\Support\Formatter;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Fluent;

use function Spatie\LaravelPdf\Support\pdf;

class RekapitulasiPemesananController extends Controller
{
  public function __invoke(DateRangeReportRequest $request)
  {
    $startDate = Carbon::parse($request->validated('start_date'))->startOfDay();
    $endDate = Carbon::parse($request->validated('end_date'))->endOfDay();
    $statusFilter = $request->input('status');

    $bookings = Booking::with([
      'user:id,name,phone',
      'packageVariant:id,package_id,name',
      'packageVariant.package:id,name',
    ])
      ->whereBetween('created_at', [$startDate, $endDate])
      ->when($statusFilter, fn ($q) => $q->where('status', $statusFilter))
      ->orderBy('created_at', 'asc')
      ->get();

    $rows = $bookings->map(function ($booking, $index) {
      $variant = $booking->packageVariant;
      $package = $variant?->package;

      $packageName = $package && $variant ? "{$package->name} - {$variant->name}" : ($package?->name ?? '-');

      $scheduleText = Formatter::dateId($booking->booking_date, 'd-m-Y') .
        ' (' . $booking->start_time->format('H:i') . ' - ' . $booking->end_time->format('H:i') . ')';

      return new Fluent([
        'no' => $index + 1,
        'booking_code' => $booking->booking_code,
        'client_name' => $booking->user?->name ?? '-',
        'phone_number' => $booking->user?->phone ?? '-',
        'package_name' => $packageName,
        'session_schedule' => $scheduleText,
        'status_label' => $booking->status->label(),
      ]);
    });

    $totalReservasi = $bookings->count();
    $printedBy = Auth::user()?->name ?? 'Administrator';
    $printDate = Formatter::dateId(Carbon::now());
    $filterText = Formatter::dateId($startDate) . ' s/d ' . Formatter::dateId($endDate);

    $statusText = 'Semua Status';
    if (!empty($statusFilter)) {
      $enumCase = BookingStatus::tryFrom($statusFilter);
      if ($enumCase) {
        $statusText = $enumCase->value;
      }
    }

    return pdf()
      ->view('pdfs.rekapitulasi-pemesanan', compact(
        'rows',
        'totalReservasi',
        'printedBy',
        'printDate',
        'filterText',
        'statusText',
      ))
      ->format('a4')
      ->landscape()
      ->margins(10, 10, 10, 10)
      ->name("laporan-rekapitulasi-pemesanan-{$startDate->format('d-m-Y')}-sd-{$endDate->format('d-m-Y')}.pdf");
  }
}
