<?php

namespace App\Domains\PdfReports\Http\Controllers;

use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Models\Booking;
use App\Http\Controllers\Controller;
use App\Support\Formatter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Fluent;

use function Spatie\LaravelPdf\Support\pdf;

class JadwalOperasionalHarianController extends Controller
{
  /**
   * Controller ini digunakan di halaman session-schedule.daily-report dan backdoor.reports.jadwal-harian.pdf
   */
  public function __invoke(Request $request)
  {
    $request->validate([
      'date' => ['nullable', 'date'],
    ]);

    // Jika ada tanggal di request, maka gunakan tanggal tersebut
    // Jika tidak ada tanggal di request, maka gunakan tanggal hari ini
    $tanggal = $request->filled('date')
      ? Carbon::parse($request->input('date'))
      : Carbon::today();

    $bookings = Booking::with([
      'user:id,name',
      'packageVariant:id,package_id,name',
      'packageVariant.package:id,name',
      'background:id,name',
    ])
      ->whereDate('booking_date', $tanggal)
      ->whereIn('status', [BookingStatus::DP_PAID, BookingStatus::SUCCESS, BookingStatus::DONE])
      ->orderBy('start_time', 'asc')
      ->get();

    $rows = $bookings->map(fn($booking, $index) => new Fluent([
      'no' => $index + 1,
      'time' => Formatter::timeRange($booking->start_time, $booking->end_time),
      'name' => $booking->user->name,
      'package' => $booking->packageVariant->package->name . ' - ' . $booking->packageVariant->name,
      'background' => $booking->background?->name ?? '-',
      'notes' => $booking->notes ?? '',
    ]));

    $printDate = Formatter::dateId($tanggal, 'd F Y');
    $printTime = Carbon::now()->format('H:i');

    return pdf()
      ->view('pdfs.jadwal-operasional-harian', compact(
        'tanggal',
        'rows',
        'printDate',
        'printTime'
      ))
      ->format('a4')
      ->portrait()
      ->margins(10, 10, 10, 10)
      ->name("jadwal-harian-{$tanggal->format('d-m-Y')}.pdf");
  }
}
