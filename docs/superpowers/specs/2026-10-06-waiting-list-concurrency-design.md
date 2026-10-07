# Spesifikasi Teknis: Fitur Waiting List & Penanganan Konkurensi
**Tanggal:** 2026-10-06
**Branch:** `feat/waiting-list-concurrency`
**Status:** Ready for Implementation

---

## Daftar Isi

1. [Ikhtisar & Tujuan](#1-ikhtisar--tujuan)
2. [Arsitektur Database (Pendekatan Ponytail)](#2-arsitektur-database-pendekatan-ponytail)
3. [Alur Kerja Backend (Checkout & Pengecekan)](#3-alur-kerja-backend-checkout--pengecekan)
4. [Alur Kerja Frontend (Redirect & UI Dashboard)](#4-alur-kerja-frontend-redirect--ui-dashboard)
5. [Mekanisme Pembersihan Data (Cron Job)](#5-mekanisme-pembersihan-data-cron-job)
6. [Mekanisme Promosi (Kenaikan Status)](#6-mekanisme-promosi-kenaikan-status)
7. [Daftar File yang Diubah/Dibuat (Beserta Kodenya)](#7-daftar-file-yang-diubahdibuat-beserta-kodenya)

---

## 1. Ikhtisar & Tujuan

Fitur ini bertugas menangani skenario *race condition* (bentrok) ekstrem di mana dua klien melakukan aksi *submit checkout* pada slot waktu yang sama dan di detik yang persis sama. 

Alih-alih memberikan pesan *error* mentah (*"Slot sudah terisi"*), sistem akan menyimpan data klien kedua sebagai antrean cadangan (**Waiting List**). Jika klien pertama batal atau transaksinya kedaluwarsa, slot tersebut akan secara otomatis ditawarkan ke klien yang ada di daftar *Waiting List*. Di Dashboard, klien juga dapat melihat posisi antreannya secara *real-time* (misal: "Waiting List #1").

---

## 2. Arsitektur Database (Pendekatan Ponytail)

Untuk menghindari *over-engineering* (membuat tabel baru `waiting_lists`, membuat relasi duplikat, dan menulis *UNION query* rumit di dashboard), sistem **tidak akan membuat tabel baru**. 

Klien yang masuk antrean tetap disimpan di tabel **`bookings`**, namun dibedakan melalui penambahan status Enum baru `WAITING_LIST`.

---

## 3. Alur Kerja Backend (Checkout & Pengecekan)

Modifikasi logika pemesanan di dalam **`app/Domains/Booking/Services/BookingService.php`** (Method `processCheckout`).

**Logika Baru:**
1. Lakukan *atomic lock* `Cache::lock`.
2. Cek apakah slot terisi.
3. Jika terisi, set `$bookingStatus = BookingStatus::WAITING_LIST`.
4. Simpan booking.
5. Jika terisi (Masuk Antrean):
   - Kirim WhatsApp Notification dengan men-dispatch *job* bawaan `SendWhatsappNotificationJob` (Bukan membuat Job baru).
   - *Return* `snap_token` bernilai `null` dan `is_waiting_list` bernilai `true`.
6. Jika normal:
   - Buat `Payment` dan *return* `snap_token` terisi.

---

## 4. Alur Kerja Frontend (Redirect & UI Dashboard)

1. **Controller Redirect:** `BookingController@checkout` akan mengembalikan JSON response dengan `is_waiting_list` bernilai true jika masuk antrean.
2. **Dashboard Query:** `DashboardService` dimodifikasi agar menarik data `WAITING_LIST` untuk tab `upcoming`.
3. **Queue Calculation:** `BookingHistoryData` DTO akan menghitung secara dinamis berapa banyak booking `WAITING_LIST` pada tanggal/jam yang sama yang dibuat *sebelumnya*, guna menampilkan teks seperti "Waiting List #1".

---

## 5. Mekanisme Pembersihan Data (Cron Job)

*Artisan command* bernama `CleanExpiredWaitingListCommand` akan dijalankan setiap tengah malam untuk menandai kedaluwarsa data *waiting list* yang tanggal sesinya sudah berlalu menjadi `CANCELLED`.

---

## 6. Mekanisme Promosi (Kenaikan Status)

Ketika ada klien yang *cancel* atau transaksi Midtrans-nya kedaluwarsa, *Event* pembatalan akan terpicu. *(Ini akan diimplementasi dalam PR terpisah setelah inti dari sistem antrian masuk berjalan).*

---

## 7. Daftar File yang Diubah/Dibuat (Beserta Kodenya)

### 7.1 `app/Domains/Booking/Enums/BookingStatus.php`
Tambahkan status Enum baru `WAITING_LIST`.

```php
<?php

namespace App\Domains\Booking\Enums;

enum BookingStatus: string
{
    case PENDING = 'Menunggu';
    case DP_PAID = 'DP Terbayar';
    case SUCCESS = 'Lunas';
    case CANCELLED = 'Batal';
    case DONE = 'Selesai';
    case WAITING_LIST = 'Waiting List';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Pembayaran',
            self::DP_PAID => 'DP Terbayar (60%)',
            self::SUCCESS => 'Lunas (100%)',
            self::CANCELLED => 'Dibatalkan',
            self::DONE => 'Selesai',
            self::WAITING_LIST => 'Masuk Antrean',
        };
    }
}
```

### 7.2 `app/Domains/Booking/Services/BookingService.php`
Ubah method `processCheckout` dan dispatch `SendWhatsappNotificationJob` langsung dari sini.

```php
	public function processCheckout(User $user, CheckoutData $data): array
	{
		$lock = Cache::lock("booking_checkout_{$data->booking_date}", 10);

		try {
			return $lock->block(5, function () use ($user, $data) {
				return DB::transaction(function () use ($user, $data) {
					$variant = PackageVariant::with('package.category')->findOrFail($data->package_variant_id);

					$preloadedAddons = null;
					if (!empty($data->addons)) {
						$addonIds = array_column($data->addons, 'addon_id');
						$preloadedAddons = Addon::select('id', 'name', 'price')
							->whereIn('id', $addonIds)->get()->keyBy('id');

						$missingAddons = array_diff($addonIds, $preloadedAddons->pluck('id')->all());
						if ($missingAddons) {
							throw new \InvalidArgumentException('Addon tidak ditemukan.');
						}
					}

					$totalPrice = $this->calculateTotal($variant, $data->addons ?? [], $preloadedAddons);

					$endTime = Carbon::parse($data->start_time)
						->addMinutes($variant->duration)
						->format('H:i');

					$isOccupied = $this->slotAvailabilityService->isSlotOccupied($data->booking_date, $data->start_time, $endTime);

					$bookingCode = $this->codeGenerator->generate(
						$variant->package->category->category_code,
						$variant->id,
						$data->booking_date,
					);

					$booking = Booking::create([
						'user_id' => $user->id,
						'package_variant_id' => $data->package_variant_id,
						'background_id' => $data->background_id ?? null,
						'booking_code' => $bookingCode,
						'booking_date' => $data->booking_date,
						'start_time' => $data->start_time,
						'end_time' => $endTime,
						'total_price' => $totalPrice,
						'payment_scheme' => $data->payment_scheme,
						'notes' => $data->notes,
						'status' => $isOccupied ? BookingStatus::WAITING_LIST : BookingStatus::PENDING,
						'source' => BookingSource::FRONTDOOR,
					]);

					if ($preloadedAddons) {
						$syncData = [];
						foreach ($data->addons as $item) {
							$addon = $preloadedAddons->get($item['addon_id']);
							$quantity = $addon && !$addon->has_quantity ? 1 : ($item['quantity'] ?? 1);
							$syncData[$item['addon_id']] = [
								'price_at_purchase' => $addon ? $addon->price : 0,
								'quantity' => $quantity,
							];
						}
						$booking->addons()->sync($syncData);
					}

					// --- KONDISI WAITING LIST ---
					if ($isOccupied) {
						$date = Carbon::parse($booking->booking_date)->translatedFormat('d F Y');
						$time = Carbon::parse($booking->start_time)->format('H:i');
						
						$message = "Halo {$user->name},\n\n"
							. "Mohon maaf, slot sesi foto pada *{$date}* pukul *{$time}* baru saja di-booking oleh klien lain di saat yang bersamaan.\n\n"
							. "Kami telah memasukkan pesanan Anda (Kode: *{$bookingCode}*) ke dalam antrean (Waiting List). Jika klien sebelumnya membatalkan pesanan atau belum membayar, slot tersebut akan kami berikan kepada Anda.\n\n"
							. "Terima kasih atas pengertiannya!\n\n"
							. "_Sistem Binary Photoworks_";

						\App\Jobs\SendWhatsappNotificationJob::dispatch($user->phone, $message);

						return [
							'booking_code' => $bookingCode, 
							'snap_token' => null, 
							'is_waiting_list' => true
						];
					}

					// --- KONDISI NORMAL ---
					$grossAmount = $data->payment_scheme === PaymentScheme::DP
						? (int) round($totalPrice * PaymentScheme::DP_RATE)
						: $totalPrice;

					$suffix = $data->payment_scheme === PaymentScheme::DP ? 'DP' : 'FULL';
					$randomString = Str::upper(Str::random(3));
					$orderId = "{$bookingCode}-{$suffix}-{$randomString}";

					$snapToken = $this->midtrans->getSnapToken(
						orderId: $orderId,
						grossAmount: $grossAmount,
						user: $user,
						bookingId: $booking->id,
						bookingCode: $bookingCode,
						packageName: $variant->package->name,
						variantName: $variant->name,
					);

					Payment::create([
						'booking_id' => $booking->id,
						'order_id' => $orderId,
						'payment_type' => null,
						'payment_purpose' => $data->payment_scheme === PaymentScheme::DP ? PaymentPurpose::DP : PaymentPurpose::LUNAS,
						'snap_token' => $snapToken,
						'snap_token_expiry' => Carbon::now()->addHour(),
						'amount' => $grossAmount,
						'status' => PaymentStatus::PENDING,
					]);

					return [
						'booking_code' => $bookingCode, 
						'snap_token' => $snapToken, 
						'is_waiting_list' => false
					];
				});
			});
		} catch (LockTimeoutException $e) {
			throw new \RuntimeException('Sistem sedang memproses pesanan di tanggal ini secara bersamaan, silakan coba lagi.');
		}
	}
```

### 7.3 `app/Domains/Booking/Http/Controllers/Frontdoor/BookingController.php`
Ubah method `checkout` untuk mengirimkan payload json berdasarkan status `is_waiting_list`.

```php
	public function checkout(CheckoutRequest $request): JsonResponse
	{
		try {
			$data = CheckoutData::fromRequest($request);
			$result = $this->bookingService->processCheckout(Auth::user(), $data);

			if ($result['is_waiting_list']) {
				return $this->successResponse(
					message: 'Slot waktu tersebut baru saja terisi di saat yang sama. Pemesanan Anda otomatis masuk ke daftar Waiting List. Detail telah dikirimkan ke WhatsApp Anda.',
					extra: [
						'snap_token' => null,
						'booking_code' => $result['booking_code'],
						'is_waiting_list' => true,
					],
				);
			}

			return $this->successResponse(
				message: 'Checkout berhasil',
				extra: [
					'snap_token' => $result['snap_token'],
					'booking_code' => $result['booking_code'],
					'is_waiting_list' => false,
				],
			);
		} catch (\RuntimeException $e) {
//... (sisanya biarkan)
```

### 7.4 `app/Domains/Booking/Services/DashboardService.php`
Pastikan `WAITING_LIST` termasuk dalam data yang ditarik untuk Dashboard di tab *upcoming*. Tambahkan `BookingStatus::WAITING_LIST` di dalam `whereIn` array.

```php
    if ($tab === 'upcoming') {
      $query->whereIn('status', [
        BookingStatus::PENDING,
        BookingStatus::DP_PAID,
        BookingStatus::SUCCESS,
        BookingStatus::WAITING_LIST // <-- Tambahkan
      ])->where('booking_date', '>=', Carbon::today()->toDateString());
    } else {
```

### 7.5 `app/Domains/Booking/DTOs/BookingHistoryData.php`
Tambahkan perhitungan dinamis untuk mengetahui posisi antrean (Waiting List #1, dst) dan izinkan pembatalan oleh user untuk status ini.

```php
    $canPay = $booking->status === BookingStatus::PENDING;
    // Izinkan Batal untuk Pending dan Waiting List
    $canCancel = in_array($booking->status, [BookingStatus::PENDING, BookingStatus::WAITING_LIST]);
    
    $statusLabel = $booking->status->label();

    if ($booking->status === BookingStatus::WAITING_LIST) {
        $queuePosition = Booking::where('booking_date', $booking->booking_date)
            ->where('start_time', $booking->start_time)
            ->where('status', BookingStatus::WAITING_LIST)
            ->where('id', '<=', $booking->id)
            ->count();
        
        $statusLabel = "Waiting List #{$queuePosition}";
    }

    return new self(
        // ... (data lain biarkan sama)
        status: $booking->status->value,
        status_label: $statusLabel,
        can_pay: $canPay,
        can_cancel: $canCancel,
        // ...
    );
```

### 7.6 `resources/views/components/frontdoor/dashboard/jadwal/booking-card.blade.php`
Warnai badge status secara khusus jika mengandung kata "Waiting List" (mengakomodir "Waiting List #1").

```blade
      <div class="flex flex-wrap items-center gap-2 mt-1.5">
        <span x-text="appointment.status_label" 
              class="text-[11px] px-2 py-0.5 border font-medium tracking-wide"
              x-bind:class="{
                  'bg-stone-200 text-stone-800 border-stone-300': appointment.status === 'Waiting List',
                  'bg-stone-100 text-stone-700 border-stone-200': appointment.status !== 'Waiting List'
              }"></span>
```

### 7.7 `app/Console/Commands/CleanExpiredWaitingListCommand.php`
*Dedicated artisan command* untuk pembersihan setiap tengah malam (tidak berubah).

```php
<?php

namespace App\Console\Commands;

use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Models\Booking;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'waitinglist:clean-expired')]
class CleanExpiredWaitingListCommand extends Command
{
    protected $signature = 'waitinglist:clean-expired';
    protected $description = 'Clean up expired waiting list bookings that have passed their booking date';

    public function handle(): int
    {
        $today = Carbon::today()->toDateString();
        
        $count = Booking::where('status', BookingStatus::WAITING_LIST)
            ->where('booking_date', '<', $today)
            ->update(['status' => BookingStatus::CANCELLED]);

        $this->info("Berhasil membersihkan {$count} data waiting list yang sudah lewat tanggal.");
        return self::SUCCESS;
    }
}
```
*(Catatan: Daftarkan di `routes/console.php` dengan `Schedule::command('waitinglist:clean-expired')->dailyAt('00:01');`)*
