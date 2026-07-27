<?php

namespace App\Domains\PdfReports\Http\Controllers;

use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Models\Booking;
use App\Domains\MasterData\Enums\DayOfWeek;
use App\Domains\Payment\Enums\PaymentStatus;
use App\Domains\PdfReports\Http\Requests\DateRangeReportRequest;
use App\Http\Controllers\Controller;
use App\Domains\PdfReports\Traits\HasPdfMetadata;
use App\Support\Formatter;
use Carbon\Carbon;
use Illuminate\Support\Fluent;

use function Spatie\LaravelPdf\Support\pdf;

class RekapitulasiPerformaHariController extends Controller
{
    use HasPdfMetadata;

  public function __invoke(DateRangeReportRequest $request)
  {
    $startDate = Carbon::parse($request->validated('start_date'))->startOfDay();
    $endDate = Carbon::parse($request->validated('end_date'))->endOfDay();

    $bookings = Booking::select(['id', 'booking_date', 'status'])
      ->with('payments:id,booking_id,status,amount')
      ->whereBetween('booking_date', [$startDate->toDateString(), $endDate->toDateString()])
      ->whereIn('status', [BookingStatus::DP_PAID, BookingStatus::SUCCESS, BookingStatus::DONE])
      ->get();

    // Group booking berdasarkan nama hari lokal Indonesia (e.g. 'Senin', 'Selasa')
    $grouped = $bookings->groupBy(
      fn($b) => Carbon::parse($b->booking_date)->locale('id')->isoFormat('dddd')
    );

    $rawRows = collect(DayOfWeek::cases())->map(function (DayOfWeek $day) use ($grouped) {
      $dayName = $day->label();
      $dayBookings = $grouped->get($dayName, collect());

      $totalSesi = $dayBookings->count();

      $totalPendapatan = $dayBookings->sum(
        fn($b) => $b->payments
          ->filter(fn($p) => $p->status === PaymentStatus::SETTLEMENT)
          ->sum('amount')
      );

      return [
        'no' => $day->value,
        'hari' => $dayName,
        'total_sesi' => $totalSesi,
        'total_pendapatan' => $totalPendapatan,
      ];
    })->values();

    $totalSemua = $rawRows->sum('total_sesi');
    $grandTotalPendapatan = Formatter::rupiah($rawRows->sum('total_pendapatan'));

    $rows = $rawRows->map(fn($row) => new Fluent([
      'no' => $row['no'],
      'hari' => $row['hari'],
      'total_sesi' => $row['total_sesi'],
      'total_pendapatan' => Formatter::rupiah($row['total_pendapatan']),
    ]));
    $filterText = Formatter::dateId($startDate) . ' s/d ' . Formatter::dateId($endDate);

    return pdf()
      ->view('pdfs.rekapitulasi-performa-hari', $this->pdfData(compact('rows', 'totalSemua', 'grandTotalPendapatan', 'filterText')))
      ->format('a4')
      ->portrait()
      ->margins(10, 10, 10, 10)
      ->name("laporan-rekapitulasi-performa-hari-{$startDate->format('d-m-Y')}-sd-{$endDate->format('d-m-Y')}.pdf");
  }
}
