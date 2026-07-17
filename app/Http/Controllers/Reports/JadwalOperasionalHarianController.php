<?php

namespace App\Http\Controllers\Reports;

use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Models\Booking;
use App\Http\Controllers\Controller;
use App\Support\Formatter;
use Carbon\Carbon;
use Illuminate\Support\Fluent;

use function Spatie\LaravelPdf\Support\pdf;

class JadwalOperasionalHarianController extends Controller
{
  public function __invoke()
  {
    $today = Carbon::today();

    $bookings = Booking::with(['user', 'packageVariant.package', 'background'])
      ->whereDate('booking_date', $today)
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

    $printDate = Formatter::dateId($today, 'l, d F Y');
    $printTime = Carbon::now()->format('H:i');

    return pdf()
      ->view('pdfs.jadwal-operasional-harian', compact('rows', 'printDate', 'printTime'))
      ->format('a4')
      ->name("jadwal-harian-{$today->format('Y-m-d')}.pdf");
  }
}
