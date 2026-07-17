<?php

namespace App\Domains\Booking\Http\Controllers\Backdoor;

use App\Domains\Booking\DTOs\SessionScheduleIndexData;
use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Models\Booking;
use App\Http\Controllers\Controller;
use App\Jobs\SendWhatsappNotificationJob;
use App\Support\Formatter;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SessionScheduleController extends Controller
{
  /**
   * Halaman Index: Daftar Jadwal Sesi Foto.
   */
  public function index(Request $request): View|JsonResponse
  {
    if ($request->wantsJson()) {
      try {
        $query = Booking::with(['user', 'packageVariant.package', 'background'])
          ->whereIn('status', [
            BookingStatus::DP_PAID,
            BookingStatus::SUCCESS,
            BookingStatus::DONE,
          ])
          ->orderBy('booking_date', 'desc')
          ->orderBy('start_time', 'desc');

        if ($search = $request->input('search')) {
          $query->where(function ($q) use ($search) {
            $q->where('booking_code', 'ilike', "%{$search}%")
              ->orWhereHas(
                'user',
                fn($u) => $u
                  ->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('phone', 'ilike', "%{$search}%")
              )
              ->orWhereHas(
                'packageVariant',
                fn($v) => $v
                  ->where('name', 'ilike', "%{$search}%")
                  ->orWhereHas('package', fn($p) => $p->where('name', 'ilike', "%{$search}%"))
              );
          });
        }

        if ($date = $request->input('date')) {
          $query->whereDate('booking_date', $date);
        }

        $limit = max(1, min((int) $request->query('limit', 10), 100));
        $bookings = $query->paginate($limit);

        $today = Carbon::today();

        $totalToday = Booking::whereDate('booking_date', $today)
          ->whereIn('status', [BookingStatus::DP_PAID, BookingStatus::SUCCESS, BookingStatus::DONE])
          ->count();
        $doneToday = Booking::whereDate('booking_date', $today)
          ->where('status', BookingStatus::DONE)
          ->count();
        $upcomingTotal = Booking::where('booking_date', '>', $today)
          ->whereIn('status', [BookingStatus::DP_PAID, BookingStatus::SUCCESS])
          ->count();

        return response()->json([
          'success' => true,
          'data' => SessionScheduleIndexData::collect($bookings->items()),
          'current_page' => $bookings->currentPage(),
          'last_page' => $bookings->lastPage(),
          'total' => $bookings->total(),
          'total_today' => $totalToday,
          'done_today' => $doneToday,
          'upcoming_total' => $upcomingTotal,
        ]);
      } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
      }
    }

    return view('backdoor.session-schedule.index');
  }

  /**
   * Simpan link Google Drive & tandai sesi selesai (DONE).
   * Hanya booking berstatus Lunas (SUCCESS) yang bisa di-update.
   */
  public function updateGdrive(Request $request, Booking $booking): JsonResponse
  {
    if ($booking->status !== BookingStatus::SUCCESS) {
      return response()->json([
        'success' => false,
        'message' => 'Booking ini tidak dalam status Lunas.',
      ], 422);
    }

    $validated = $request->validate([
      'gdrive_link' => ['required', 'url', 'starts_with:http://,https://'],
    ], [
      'gdrive_link.required'    => 'Link Google Drive wajib diisi.',
      'gdrive_link.url'         => 'Format link tidak valid.',
      'gdrive_link.starts_with' => 'Link harus diawali dengan http:// atau https://',
    ]);

    try {
      $booking->load(['user', 'packageVariant.package']);
      $booking->update([
        'gdrive_link' => $validated['gdrive_link'],
        'status'  => BookingStatus::DONE,
      ]);

      $isSendWa = $request->boolean('send_wa_notification', true);
      if ($isSendWa) {
        SendWhatsappNotificationJob::dispatch(
          $booking->user->phone,
          $this->buildGdriveMessage($booking)
        );
      }

      return response()->json([
        'success' => true,
        'message' => 'Sesi berhasil ditandai selesai.'
          . ($isSendWa ? " Notifikasi WA dikirim ke {$booking->user->phone}." : ''),
        'data' => SessionScheduleIndexData::fromModel($booking->refresh()),
      ]);
    } catch (\Exception $e) {
      return response()->json(['success' => false, 'message' => 'Terjadi kesalahan server.'], 500);
    }
  }

  /**
   * Pesan WA untuk notifikasi pengiriman link GDrive hasil foto.
   */
  private function buildGdriveMessage(Booking $booking): string
  {
    $code = $booking->booking_code;
    $user = $booking->user?->name;
    $package = $booking->packageVariant?->package?->name;
    $variant = $booking->packageVariant?->name;
    $gdriveLink = $booking->gdrive_link;
    $bookingDate = Formatter::dateId($booking->booking_date, 'l, d F Y');

    return <<<TEXT
Halo {$user}, sesi foto Anda telah selesai!

Berikut adalah rincian pesanan Anda:
*Kode Booking* : {$code}
*Paket* : {$package} - {$variant}
*Tanggal Sesi* : {$bookingDate}

Berikut adalah Link Google Drive untuk mengunduh hasil foto Anda:
{$gdriveLink}

Terima kasih telah mempercayakan momen berharga Anda kepada Binary Photoworks!
TEXT;
  }
}
