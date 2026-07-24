<?php

namespace App\Domains\AdminDashboard\Services;

use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Models\Booking;
use App\Domains\Payment\Models\Payment;
use App\Domains\Payment\Enums\PaymentStatus;
use App\Domains\User\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use App\Support\Formatter;
use App\Domains\User\Enums\RoleType;

class DashboardService
{
  /**
   * Statistik dalam bentuk card
   */
  public function getStats(): array
  {
    $now = Carbon::now();

    return [
      // total reservasi per bulan ini, kecuali status CANCELLED
      'totalReservasi'  => Booking::whereMonth('created_at', $now->month)
        ->whereYear('created_at', $now->year)
        ->where('status', '!=', BookingStatus::CANCELLED)
        ->count(),

      // total pendapatan per bulan ini
      'totalPendapatan' => Formatter::rupiah(
        Payment::whereMonth('pay_date', $now->month)
          ->whereYear('pay_date', $now->year)
          ->where('status', PaymentStatus::SETTLEMENT->value)
          ->sum('amount')
      ),

      // total klien, role = user
      'totalKlien' => User::role(RoleType::USER->value)->count(),

      // sesi foto menunggu, ambil booking berdasarkan tanggal hari ini yang statusnya PENDING
      'sesiMenunggu' => Booking::whereDate('booking_date', $now->toDateString())
        ->where('status', BookingStatus::PENDING)
        ->count(),
    ];
  }

  /**
   * Function untuk mengambil data analytics
   */
  public function getAnalytics(int $year): array
  {
    return [
      'year' => $year,
      'booking_trend' => $this->getBookingTrend($year),
      'revenue_trend' => $this->getRevenueTrend($year),
      'package_proportion' => $this->getPackageProportion($year),
      'addon_proportion' => $this->getAddonProportion($year),
    ];
  }

  /**
   * Query untuk ambil data tahun yang tersedia
   * Digunakan pada filter tahun untuk statistik (chart / grafik)
   */
  public function getAvailableYears(): array
  {
    $years = Booking::selectRaw('EXTRACT(YEAR FROM created_at)::int as year')
      ->union(Booking::selectRaw('EXTRACT(YEAR FROM booking_date)::int as year'))
      ->distinct()
      ->orderBy('year', 'desc')
      ->pluck('year')
      ->toArray();

    return empty($years) ? [(int) date('Y')] : $years;
  }

  /**
   * Statistik booking per bulan
   * Filter berdasarkan tahun
   */
  public function getBookingTrend(int $year): array
  {
    $raw = Booking::whereYear('created_at', $year)
      ->where('status', '!=', BookingStatus::CANCELLED)
      ->selectRaw('EXTRACT(MONTH FROM created_at)::int as month, COUNT(id) as total')
      ->groupBy('month')
      ->orderBy('month')
      ->pluck('total', 'month')
      ->toArray();

    $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $data = array_map(fn($m) => (int) ($raw[$m] ?? 0), range(1, 12));

    return ['labels' => $months, 'data' => $data];
  }

  /**
   * Ambil pendapatan per bulan
   * Filter berdasarkan tahun
   */
  public function getRevenueTrend(int $year): array
  {
    $raw = Payment::whereYear('pay_date', $year)
      ->where('status', PaymentStatus::SETTLEMENT->value)
      ->selectRaw('EXTRACT(MONTH FROM pay_date)::int as month, SUM(amount) as total')
      ->groupBy('month')
      ->orderBy('month')
      ->pluck('total', 'month')
      ->toArray();

    $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $data = array_map(fn($m) => (int) ($raw[$m] ?? 0), range(1, 12));

    return ['labels' => $months, 'data' => $data];
  }

  /**
   * Proporsi paket terlaris berdasarkan kategori
   * Filter berdasarkan tahun
   */
  public function getPackageProportion(int $year): array
  {
    $results = Booking::whereYear('bookings.booking_date', $year)
      ->where('bookings.status', '!=', BookingStatus::CANCELLED)
      ->join('package_variants', 'bookings.package_variant_id', '=', 'package_variants.id')
      ->join('packages', 'package_variants.package_id', '=', 'packages.id')
      ->join('categories', 'packages.category_id', '=', 'categories.id')
      ->selectRaw('categories.name as label, COUNT(bookings.id) as total')
      ->groupBy('categories.name')
      ->orderBy('total', 'desc')
      ->get();

    return [
      'labels' => $results->pluck('label')->toArray(),
      'data' => $results->pluck('total')->map(fn($v) => (int) $v)->toArray(),
    ];
  }

  /**
   * Proporsi addon terlaris
   * Filter berdasarkan tahun
   */
  public function getAddonProportion(int $year): array
  {
    $results = DB::table('addon_booking')
      ->join('bookings', 'addon_booking.booking_id', '=', 'bookings.id')
      ->join('addons', 'addon_booking.addon_id', '=', 'addons.id')
      ->whereYear('bookings.booking_date', $year)
      ->where('bookings.status', '!=', BookingStatus::CANCELLED)
      ->selectRaw('addons.name as label, SUM(addon_booking.quantity) as total')
      ->groupBy('addons.name')
      ->orderBy('total', 'desc')
      ->get();

    return [
      'labels' => $results->pluck('label')->toArray(),
      'data' => $results->pluck('total')->map(fn($v) => (int) $v)->toArray(),
    ];
  }

  /**
   * Data untuk table jadwal pemotretan hari ini
   * Ambil data booking berdasarkan tanggal hari ini
   * Untuk status ambil berdasarkan status yang bukan PENDING dan CANCELLED
   */
  public function getTodaySchedule(): Collection
  {
    return Booking::whereDate('booking_date', Carbon::today())
      ->whereNotIn('status', [BookingStatus::PENDING, BookingStatus::CANCELLED])
      ->with(['user', 'packageVariant.package'])
      ->orderBy('start_time', 'asc')
      ->limit(5)
      ->get();
  }

  /**
   * Ambil 5 data booking terakhir
   */
  public function getRecentBookings(): Collection
  {
    return Booking::with(['user'])
      ->orderBy('created_at', 'desc')
      ->limit(5)
      ->get();
  }
}
