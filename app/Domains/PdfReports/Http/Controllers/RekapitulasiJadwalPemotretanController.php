<?php

namespace App\Domains\PdfReports\Http\Controllers;

use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Models\Booking;
use App\Domains\PdfReports\Http\Requests\DateRangeReportRequest;
use App\Http\Controllers\Controller;
use App\Domains\PdfReports\Traits\HasPdfMetadata;
use App\Support\Formatter;
use Carbon\Carbon;
use Illuminate\Support\Fluent;

use function Spatie\LaravelPdf\Support\pdf;

class RekapitulasiJadwalPemotretanController extends Controller
{
    use HasPdfMetadata;

  public function __invoke(DateRangeReportRequest $request)
  {
    $startDate = Carbon::parse($request->validated('start_date'))->startOfDay();
    $endDate = Carbon::parse($request->validated('end_date'))->endOfDay();

    $bookings = Booking::select([
      'id',
      'user_id',
      'package_variant_id',
      'background_id',
      'booking_date',
      'start_time',
      'end_time',
      'notes',
      'status'
    ])
      ->with([
        'user:id,name',
        'packageVariant:id,package_id,name',
        'packageVariant.package:id,name',
        'background:id,name',
      ])
      ->whereBetween('booking_date', [$startDate->toDateString(), $endDate->toDateString()])
      ->whereIn('status', [BookingStatus::DP_PAID, BookingStatus::SUCCESS])
      ->orderBy('booking_date', 'asc')
      ->orderBy('start_time', 'asc')
      ->get();

    $rows = $bookings->map(function ($booking, $index) {
      return new Fluent([
        'no' => $index + 1,
        'tanggal' => Formatter::dateId($booking->booking_date, 'd-m-Y'),
        'nama' => $booking->user?->name ?? '-',
        'jam' => Formatter::timeRange($booking->start_time, $booking->end_time),
        'package' => $booking->packageVariant->package->name . ' - ' . $booking->packageVariant->name,
        'background' => $booking->background?->name ?? '-',
        'keterangan' => $booking->notes ?? '',
      ]);
    });

    $totalJadwal = $bookings->count();
    $filterText = Formatter::dateId($startDate) . ' s/d ' . Formatter::dateId($endDate);

    return pdf()
      ->view('pdfs.rekapitulasi-jadwal-pemotretan', $this->pdfData(compact('rows', 'totalJadwal', 'filterText' )))
      ->format('a4')
      ->portrait()
      ->margins(10, 10, 10, 10)
      ->name("laporan-rekapitulasi-jadwal-pemotretan-{$startDate->format('d-m-Y')}-sd-{$endDate->format('d-m-Y')}.pdf");
  }
}
