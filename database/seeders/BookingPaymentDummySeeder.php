<?php

namespace Database\Seeders;

use App\Domains\Booking\Enums\BookingSource;
use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Enums\PaymentScheme;
use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\Services\BookingCodeGenerator;
use App\Domains\MasterData\Enums\DayOfWeek;
use App\Domains\MasterData\Models\Addon;
use App\Domains\MasterData\Models\Background;
use App\Domains\MasterData\Models\PackageVariant;
use App\Domains\MasterData\Models\Schedule;
use App\Domains\Payment\Enums\PaymentPurpose;
use App\Domains\Payment\Enums\PaymentStatus;
use App\Domains\Payment\Models\Payment;
use App\Domains\User\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingPaymentDummySeeder extends Seeder
{
  public function run()
  {
    $this->command->info('1. Memuat data dasar (User, Schedule, Paket Studio, Addons)...');

    $users = User::role('user')->get();
    if ($users->isEmpty()) {
      $this->command->error('Tidak ada user dengan role "user". Harap jalankan UserSeeder terlebih dahulu.');
      return;
    }

    // Nonaktifkan pencatatan Spatie ActivityLog selama proses seeding dummy (performa instan)
    if (function_exists('activity')) {
      activity()->disableLogging();
    }

    // Ambil hari-hari aktif dari tabel schedules (DayOfWeek enum ->value)
    $activeDays = Schedule::where('is_active', true)
      ->get()
      ->map(fn($s) => $s->day instanceof DayOfWeek ? $s->day->value : (int)$s->day)
      ->toArray();

    if (empty($activeDays)) {
      $activeDays = [1, 2, 3, 4, 5, 6]; // Default Senin - Sabtu
    }

    // Filter HANYA paket studio (is_whatsapp_only = false)
    $packageVariants = PackageVariant::where('is_whatsapp_only', false)
      ->where('is_active', true)
      ->with('package.category')
      ->get();

    $backgrounds = Background::where('is_active', true)->get();
    $addons = Addon::where('is_active', true)->get();

    if ($packageVariants->isEmpty() || $backgrounds->isEmpty()) {
      $this->command->error('Tidak ada paket studio atau background. Jalankan MasterData seeder dulu.');
      return;
    }

    $codeGenerator = app(BookingCodeGenerator::class);

    $startDate = Carbon::create(2025, 7, 1);
    $endDate = Carbon::create(2026, 7, 20);

    // Kumpulkan seluruh tanggal operasional aktif
    $validDates = [];
    $currDate = $startDate->copy();
    while ($currDate->lte($endDate)) {
      if (in_array($currDate->dayOfWeekIso, $activeDays)) {
        $validDates[] = $currDate->copy();
      }
      $currDate->addDay();
    }

    if (empty($validDates)) {
      $this->command->error('Tidak ada tanggal operasional yang valid.');
      return;
    }

    // Slot jam studio (09:00 - 19:00)
    $slotHours = [9, 10, 11, 13, 14, 15, 16, 17, 18];
    $totalBookings = 175;

    $this->command->info("2. Generate {$totalBookings} transaksi Booking & Payment (Juli 2025 - Juli 2026)...");

    // Inisialisasi Progress Bar Console
    $bar = $this->command->getOutput()->createProgressBar($totalBookings);
    $bar->start();

    DB::transaction(function () use (
      $totalBookings,
      $validDates,
      $slotHours,
      $packageVariants,
      $backgrounds,
      $addons,
      $codeGenerator,
      $users,
      $bar
    ) {
      for ($i = 0; $i < $totalBookings; $i++) {
        // 1. Ambil 1 tanggal operasional valid secara acak
        $bookingDate = $validDates[array_rand($validDates)]->copy();

        // 2. created_at dibuat 2 s/d 30 hari SEBELUM booking_date
        $createdAt = $bookingDate->copy()->subDays(rand(2, 30))->setTime(rand(8, 20), rand(0, 59), rand(0, 59));

        // 3. Ambil Varian Paket Studio & Category Code
        $variant = $packageVariants->random();
        $categoryCode = $variant->package?->category?->category_code ?? 'PSN';
        $bg = $backgrounds->random();

        // 4. Tentukan Jam Sesi berdasarkan Slot Operasional
        $startHour = $slotHours[array_rand($slotHours)];
        $startTime = $bookingDate->copy()->setTime($startHour, 0, 0);
        $endTime = $startTime->copy()->addMinutes($variant->duration ?? 60);

        // 5. Generate Booking Code Resmi
        $bookingCode = $codeGenerator->generate($categoryCode, $variant->id, $bookingDate->format('Y-m-d'));

        // 6. Pilih Addons secara acak & Hitung Total Presisi
        $selectedAddons = [];
        $addonTotal = 0;
        if ($addons->isNotEmpty() && rand(1, 100) <= 60) {
          $pickedAddons = $addons->random(rand(1, min(2, $addons->count())));
          foreach ($pickedAddons as $addonItem) {
            $qty = $addonItem->has_quantity ? rand(1, 3) : 1;
            $selectedAddons[$addonItem->id] = [
              'price_at_purchase' => $addonItem->price,
              'quantity' => $qty,
            ];
            $addonTotal += ($addonItem->price * $qty);
          }
        }

        $totalPrice = $variant->price + $addonTotal;

        // 7. Tentukan Status & Skema Pembayaran
        $isPastDate = $bookingDate->isBefore(Carbon::create(2026, 7, 22));

        if ($isPastDate) {
          $randPercent = rand(1, 100);
          if ($randPercent <= 75) {
            $status = BookingStatus::DONE->value;
          } elseif ($randPercent <= 90) {
            $status = BookingStatus::SUCCESS->value;
          } else {
            $status = BookingStatus::CANCELLED->value;
          }
        } else {
          $randPercent = rand(1, 100);
          if ($randPercent <= 50) {
            $status = BookingStatus::DP_PAID->value;
          } elseif ($randPercent <= 80) {
            $status = BookingStatus::SUCCESS->value;
          } else {
            $status = BookingStatus::PENDING->value;
          }
        }

        $scheme = ($status === BookingStatus::DP_PAID->value)
          ? PaymentScheme::DP->value
          : (rand(1, 100) <= 75 ? PaymentScheme::DP->value : PaymentScheme::LUNAS->value);

        // 8. Simpan Booking
        $booking = Booking::create([
          'user_id' => $users->random()->id,
          'package_variant_id' => $variant->id,
          'background_id' => $bg->id,
          'booking_code' => $bookingCode,
          'booking_date' => $bookingDate,
          'start_time' => $startTime,
          'end_time' => $endTime,
          'total_price' => $totalPrice,
          'payment_scheme' => $scheme,
          'status' => $status,
          'reschedule_count' => 0,
          'notes' => 'Transaksi dummy otomatis',
          'gdrive_link' => ($status === BookingStatus::DONE->value) ? 'https://drive.google.com/dummy-photos' : null,
          'source' => (rand(1, 100) <= 80 ? BookingSource::FRONTDOOR->value : BookingSource::MANUAL->value),
          'created_at' => $createdAt,
          'updated_at' => $createdAt,
        ]);

        if (!empty($selectedAddons)) {
          $booking->addons()->attach($selectedAddons);
        }

        // 9. Generate Payments dengan perhitungan DP 60% & Pelunasan 40%
        $amountDP = (int) round($totalPrice * 0.60);
        $amountPelunasan = $totalPrice - $amountDP;

        if ($scheme === PaymentScheme::LUNAS->value) {
          if (in_array($status, [BookingStatus::SUCCESS->value, BookingStatus::DONE->value])) {
            $payDate = $createdAt->copy()->addMinutes(rand(5, 45));
            $this->createPayment($booking, PaymentPurpose::LUNAS, $totalPrice, PaymentStatus::SETTLEMENT, 'qris', $payDate, $createdAt);
          } elseif ($status === BookingStatus::PENDING->value) {
            $this->createPayment($booking, PaymentPurpose::LUNAS, $totalPrice, PaymentStatus::PENDING, 'qris', null, $createdAt);
          } elseif ($status === BookingStatus::CANCELLED->value) {
            $this->createPayment($booking, PaymentPurpose::LUNAS, $totalPrice, PaymentStatus::CANCELLED, 'qris', null, $createdAt);
          }
        } else {
          if ($status === BookingStatus::PENDING->value) {
            $this->createPayment($booking, PaymentPurpose::DP, $amountDP, PaymentStatus::PENDING, 'qris', null, $createdAt);
          } elseif ($status === BookingStatus::DP_PAID->value) {
            $dpPayDate = $createdAt->copy()->addMinutes(rand(5, 45));
            $this->createPayment($booking, PaymentPurpose::DP, $amountDP, PaymentStatus::SETTLEMENT, 'qris', $dpPayDate, $createdAt);
            $this->createPayment($booking, PaymentPurpose::PELUNASAN, $amountPelunasan, PaymentStatus::PENDING, 'manual', null, $createdAt);
          } elseif (in_array($status, [BookingStatus::SUCCESS->value, BookingStatus::DONE->value])) {
            $dpPayDate = $createdAt->copy()->addMinutes(rand(5, 45));
            $lunasPayDate = $bookingDate->copy()->subHours(rand(1, 3));
            $this->createPayment($booking, PaymentPurpose::DP, $amountDP, PaymentStatus::SETTLEMENT, 'qris', $dpPayDate, $createdAt);
            $this->createPayment($booking, PaymentPurpose::PELUNASAN, $amountPelunasan, PaymentStatus::SETTLEMENT, 'manual', $lunasPayDate, $lunasPayDate);
          } elseif ($status === BookingStatus::CANCELLED->value) {
            $this->createPayment($booking, PaymentPurpose::DP, $amountDP, PaymentStatus::CANCELLED, 'qris', null, $createdAt);
          }
        }

        // Advance progress bar per transaksi
        $bar->advance();
      }
    });

    $bar->finish();
    $this->command->newLine(2);

    // Aktifkan kembali Spatie ActivityLog setelah seeding selesai
    if (function_exists('activity')) {
      activity()->enableLogging();
    }

    $this->command->info("SELESAI! Berhasil membuat {$totalBookings} data dummy Booking dan Payment!");
  }

  private function createPayment($booking, $purpose, $amount, $status, $paymentType, $payDate, $createdAt)
  {
    Payment::create([
      'booking_id' => $booking->id,
      'order_id' => 'ORD-' . $booking->booking_code . '-' . strtoupper(Str::random(4)),
      'payment_type' => $paymentType,
      'payment_purpose' => $purpose->value,
      'amount' => $amount,
      'status' => $status->value,
      'pay_date' => $payDate,
      'snap_token' => Str::random(20),
      'snap_token_expiry' => $createdAt->copy()->addHours(24),
      'created_at' => $createdAt,
      'updated_at' => $payDate ?? $createdAt,
    ]);
  }
}
