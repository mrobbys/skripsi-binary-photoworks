# Spec: Booking Flow — Client-Side Multi-Step Wizard

**Tanggal:** 2026-06-27  
**Scope:** Backend (Controller, Service, DTO, FormRequest, Repository, Jobs) + Frontend (Blade, Alpine.js, JS Modules, Flatpickr, Midtrans Snap) + Notifikasi (Fonnte WhatsApp)  
**Stack:** Laravel 13 · PHP 8.3 · Alpine.js · Tailwind CSS v4 · Axios · Flatpickr · Midtrans Snap SDK · Fonnte API · `spatie/laravel-data`  
**Design System:** ThoughtStream (flat, no rounded corners, stone/warm-black palette, border separators)

---

## Daftar Isi

1. [Struktur Direktori File Baru](#1-struktur-direktori-file-baru)
2. [Route](#2-route)
3. [DTOs](#3-dtos)
4. [Repository](#4-repository)
5. [Service — BookingService](#5-service--bookingservice)
6. [Service — BookingCodeGenerator](#6-service--bookingcodegenerator)
7. [Service — MidtransService](#7-service--midtransservice)
8. [Service — FonnteWhatsappService](#8-service--fonntewhatsappservice)
9. [Jobs](#9-jobs)
10. [Form Requests](#10-form-requests)
11. [Controller — BookingController](#11-controller--bookingcontroller)
12. [Controller — WebhookController](#12-controller--webhookcontroller)
13. [Frontend — JS Modules](#13-frontend--js-modules)
14. [Frontend — Blade Views](#14-frontend--blade-views)
15. [Blade Components](#15-blade-components)
16. [Registrasi & Catatan Implementasi](#16-registrasi--catatan-implementasi)

---

## 1. Struktur Direktori File Baru

```
app/Domains/Booking/
├── DTOs/
│   ├── BookingData.php                ← BARU
│   └── CheckoutData.php               ← BARU
├── Http/
│   ├── Controllers/Frontdoor/
│   │   ├── BookingController.php      ← MODIFIKASI
│   │   └── WebhookController.php      ← BARU
│   └── Requests/
│       └── CheckoutRequest.php        ← BARU
├── Repositories/
│   └── BookingRepository.php          ← BARU
└── Services/
    ├── BookingService.php             ← BARU
    ├── BookingCodeGenerator.php       ← BARU
    └── MidtransService.php            ← BARU

app/Services/
└── FonnteWhatsappService.php          ← BARU

app/Jobs/
└── SendWhatsappNotificationJob.php    ← BARU

routes/frontdoor/
├── booking.php                        ← MODIFIKASI
└── services.php                       ← BARU

resources/js/features/booking/
├── booking.js       ← BARU (Alpine.data entry point)
├── useState.js      ← BARU
├── useCalendar.js   ← BARU (Flatpickr + debounced slot fetch)
├── useAddons.js     ← BARU
└── useCheckout.js   ← BARU (Axios + Midtrans Snap)

resources/views/frontdoor/
├── services/index.blade.php           ← BARU
└── booking/
    ├── flow.blade.php                 ← BARU
    ├── script.blade.php               ← BARU (co-located)
    ├── success.blade.php              ← BARU
    └── steps/
        ├── step-1-variant.blade.php
        ├── step-2-schedule.blade.php
        ├── step-3-addons.blade.php
        └── step-4-summary.blade.php

resources/views/components/frontdoor/booking/
├── package-card.blade.php
├── variant-radio.blade.php
├── background-thumb.blade.php
├── time-slot-button.blade.php
├── addon-card.blade.php
└── step-indicator.blade.php
```

---

## 2. Route

### `routes/frontdoor/services.php` ← BARU

```php
<?php

use App\Domains\Booking\Http\Controllers\Frontdoor\BookingController;
use Illuminate\Support\Facades\Route;

Route::get('/services', [BookingController::class, 'services'])
    ->name('frontdoor.services.index');
```

### `routes/frontdoor/booking.php` ← MODIFIKASI PENUH

```php
<?php

use App\Domains\Booking\Http\Controllers\Frontdoor\BookingController;
use App\Domains\Booking\Http\Controllers\Frontdoor\WebhookController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])
    ->prefix('booking')
    ->name('frontdoor.booking.')
    ->group(function () {
        Route::get('/{package:slug}', [BookingController::class, 'flow'])->name('flow');
        Route::get('/api/slots', [BookingController::class, 'getAvailableSlots'])->name('api.slots');
        Route::post('/checkout', [BookingController::class, 'checkout'])->name('checkout');
        Route::get('/success/{bookingCode}', [BookingController::class, 'success'])->name('success');
    });

// Webhook Midtrans — bebas auth & CSRF
Route::post('/api/payments/webhook', [WebhookController::class, 'handle'])
    ->name('payments.webhook');
```

> **CSRF Exception** — tambahkan di `bootstrap/app.php`:
> ```php
> ->withMiddleware(function (Middleware $middleware) {
>     $middleware->validateCsrfTokens(except: ['api/payments/webhook']);
> })
> ```

> **Daftarkan di `routes/frontdoor/frontdoor.php`:**
> ```php
> require __DIR__.'/services.php';
> // booking.php sudah ada
> ```

---

## 3. DTOs

### `app/Domains/Booking/DTOs/CheckoutData.php`

```php
<?php

namespace App\Domains\Booking\DTOs;

use Spatie\LaravelData\Data;

class CheckoutData extends Data
{
    public function __construct(
        public readonly int    $package_variant_id,
        public readonly ?int   $background_id,
        public readonly string $booking_date,   // Y-m-d
        public readonly string $start_time,     // H:i
        public readonly string $payment_scheme, // 'lunas' | 'dp'
        /** @var array<int, array{addon_id: int, quantity: int}> */
        public readonly array  $addons,
    ) {}
}
```

### `app/Domains/Booking/DTOs/BookingData.php`

```php
<?php

namespace App\Domains\Booking\DTOs;

use Spatie\LaravelData\Data;

class BookingData extends Data
{
    public function __construct(
        public readonly int    $user_id,
        public readonly int    $package_variant_id,
        public readonly ?int   $background_id,
        public readonly string $booking_code,
        public readonly string $booking_date,
        public readonly string $start_time,
        public readonly string $end_time,
        public readonly int    $total_price,
        public readonly string $payment_scheme,
        public readonly string $status,
    ) {}
}
```

---

## 4. Repository

### `app/Domains/Booking/Repositories/BookingRepository.php`

```php
<?php

namespace App\Domains\Booking\Repositories;

use App\Domains\Booking\DTOs\BookingData;
use App\Domains\Booking\Models\Booking;
use Illuminate\Support\Collection;

class BookingRepository
{
    /**
     * Algoritma overlap: start_a < end_b AND end_a > start_b
     */
    public function isSlotOccupied(string $date, string $startTime, string $endTime): bool
    {
        return Booking::where('booking_date', $date)
            ->where('status', '!=', 'Batal')
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime)
                      ->where('end_time', '>', $startTime);
                });
            })->exists();
    }

    public function getOccupiedSlotsByDate(string $date): Collection
    {
        return Booking::select('start_time', 'end_time')
            ->where('booking_date', $date)
            ->where('status', '!=', 'Batal')
            ->get();
    }

    public function create(BookingData $data): Booking
    {
        return Booking::create([
            'user_id'            => $data->user_id,
            'package_variant_id' => $data->package_variant_id,
            'background_id'      => $data->background_id,
            'booking_code'       => $data->booking_code,
            'booking_date'       => $data->booking_date,
            'start_time'         => $data->start_time,
            'end_time'           => $data->end_time,
            'total_price'        => $data->total_price,
            'payment_scheme'     => $data->payment_scheme,
            'status'             => $data->status,
        ]);
    }

    public function findByCode(string $code): ?Booking
    {
        return Booking::with(['user', 'packageVariant.package', 'background', 'addons', 'payments'])
            ->where('booking_code', $code)
            ->first();
    }
}
```

---

## 5. Service — BookingService

### `app/Domains/Booking/Services/BookingService.php`

```php
<?php

namespace App\Domains\Booking\Services;

use App\Domains\Booking\DTOs\BookingData;
use App\Domains\Booking\DTOs\CheckoutData;
use App\Domains\Booking\Repositories\BookingRepository;
use App\Domains\MasterData\Models\Addon;
use App\Domains\MasterData\Models\PackageVariant;
use App\Domains\Payment\Models\Payment;
use App\Domains\User\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function __construct(
        private readonly BookingRepository    $repository,
        private readonly BookingCodeGenerator $codeGenerator,
        private readonly MidtransService      $midtrans,
    ) {}

    public function calculateTotal(int $variantId, array $addons): int
    {
        $variant = PackageVariant::findOrFail($variantId);
        $base    = $variant->price;

        $addonTotal = collect($addons)->sum(function (array $item) {
            $addon = Addon::findOrFail($item['addon_id']);
            return $addon->price * ($item['quantity'] ?? 1);
        });

        return $base + $addonTotal;
    }

    public function processCheckout(User $user, CheckoutData $data): array
    {
        return DB::transaction(function () use ($user, $data) {
            $variant    = PackageVariant::with('package.category')->findOrFail($data->package_variant_id);
            $totalPrice = $this->calculateTotal($data->package_variant_id, $data->addons);

            $endTime = Carbon::parse($data->start_time)
                ->addMinutes($variant->duration)
                ->format('H:i');

            // Final server-side double-booking guard
            if ($this->repository->isSlotOccupied($data->booking_date, $data->start_time, $endTime)) {
                throw new \RuntimeException('Slot waktu sudah terisi. Silakan pilih jam lain.');
            }

            $bookingCode = $this->codeGenerator->generate(
                $variant->package->category->category_code,
                $variant->id,
                $data->booking_date,
            );

            $booking = $this->repository->create(new BookingData(
                user_id:            $user->id,
                package_variant_id: $data->package_variant_id,
                background_id:      $data->background_id,
                booking_code:       $bookingCode,
                booking_date:       $data->booking_date,
                start_time:         $data->start_time,
                end_time:           $endTime,
                total_price:        $totalPrice,
                payment_scheme:     $data->payment_scheme,
                status:             'Menunggu',
            ));

            // Attach addons ke pivot dengan price_at_purchase snapshot
            $syncData = collect($data->addons)->mapWithKeys(function (array $item) {
                $addon = Addon::findOrFail($item['addon_id']);
                return [
                    $item['addon_id'] => [
                        'price_at_purchase' => $addon->price,
                        'quantity'          => $item['quantity'] ?? 1,
                    ],
                ];
            })->all();
            $booking->addons()->sync($syncData);

            $grossAmount = $data->payment_scheme === 'dp'
                ? (int) round($totalPrice * 0.60)
                : $totalPrice;

            $snapToken = $this->midtrans->getSnapToken(
                orderId:     $bookingCode,
                grossAmount: $grossAmount,
                user:        $user,
                booking:     $booking,
            );

            Payment::create([
                'booking_id'      => $booking->id,
                'order_id'        => $bookingCode,
                'payment_type'    => 'online',
                'payment_purpose' => $data->payment_scheme === 'dp' ? 'dp' : 'lunas',
                'snap_token'      => $snapToken,
                'amount'          => $grossAmount,
                'status'          => 'Pending',
            ]);

            return ['booking_code' => $bookingCode, 'snap_token' => $snapToken];
        });
    }
}
```

---

## 6. Service — BookingCodeGenerator

### `app/Domains/Booking/Services/BookingCodeGenerator.php`

```php
<?php

namespace App\Domains\Booking\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;

class BookingCodeGenerator
{
    /**
     * Format: BPW-{CAT_CODE}{VAR_ID_02d}-{YYMMDD}-{RAND3}
     * Contoh: BPW-WSD01-260530-7AX
     */
    public function generate(string $categoryCode, int $variantId, string $bookingDate): string
    {
        $packageSegment = strtoupper($categoryCode) . str_pad($variantId, 2, '0', STR_PAD_LEFT);
        $dateSegment    = Carbon::parse($bookingDate)->format('ymd');
        $randomSegment  = Str::upper(Str::random(3));

        return "BPW-{$packageSegment}-{$dateSegment}-{$randomSegment}";
    }
}
```

---

## 7. Service — MidtransService

### `app/Domains/Booking/Services/MidtransService.php`

```php
<?php

namespace App\Domains\Booking\Services;

use App\Domains\Booking\Models\Booking;
use App\Domains\User\Models\User;
use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Facades\Http;

class MidtransService
{
    public function __construct(
        #[Config('services.midtrans.server_key')] private string $serverKey,
        #[Config('services.midtrans.snap_url')]   private string $snapUrl,
    ) {}

    public function getSnapToken(string $orderId, int $grossAmount, User $user, Booking $booking): string
    {
        $payload = [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email'      => $user->email,
                'phone'      => $user->phone,
            ],
            'callbacks' => [
                'finish' => route('frontdoor.booking.success', $orderId),
            ],
        ];

        $response = Http::withBasicAuth($this->serverKey, '')
            ->post("{$this->snapUrl}/transactions", $payload)
            ->throw()
            ->json();

        return $response['token'];
    }
}
```

> **`config/services.php`:**
> ```php
> 'midtrans' => [
>     'server_key' => env('MIDTRANS_SERVER_KEY'),
>     'client_key' => env('MIDTRANS_CLIENT_KEY'),
>     'snap_url'   => env('MIDTRANS_SNAP_URL', 'https://app.sandbox.midtrans.com/snap/v1'),
> ],
> ```

---

## 8. Service — FonnteWhatsappService

### `app/Services/FonnteWhatsappService.php`

```php
<?php

namespace App\Services;

use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteWhatsappService
{
    public function __construct(
        #[Config('services.fonnte.token')] private string $token,
    ) {}

    public function send(string $phone, string $message): bool
    {
        $normalized = $this->normalizePhone($phone);

        $response = Http::withHeaders(['Authorization' => $this->token])
            ->post('https://api.fonnte.com/send', [
                'target'  => $normalized,
                'message' => $message,
            ]);

        if (! $response->ok()) {
            Log::channel('whatsapp')->error('Fonnte send failed', [
                'phone'    => $normalized,
                'response' => $response->body(),
            ]);
            return false;
        }

        return true;
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        return str_starts_with($phone, '0') ? '62' . substr($phone, 1) : $phone;
    }
}
```

> **`config/services.php`:** `'fonnte' => ['token' => env('FONNTE_TOKEN')]`

---

## 9. Jobs

### `app/Jobs/SendWhatsappNotificationJob.php`

```php
<?php

namespace App\Jobs;

use App\Services\FonnteWhatsappService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\Attributes\Timeout;

#[Tries(3)]
#[Timeout(60)]
class SendWhatsappNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $phone,
        public readonly string $message,
    ) {}

    public function handle(FonnteWhatsappService $service): void
    {
        $service->send($this->phone, $this->message);
    }
}
```

---

## 10. Form Requests

### `app/Domains/Booking/Http/Requests/CheckoutRequest.php`

```php
<?php

namespace App\Domains\Booking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'package_variant_id' => ['required', 'integer', 'exists:package_variants,id'],
            'background_id'      => ['nullable', 'integer', 'exists:backgrounds,id'],
            'booking_date'       => ['required', 'date', 'after_or_equal:today'],
            'start_time'         => ['required', 'date_format:H:i'],
            'payment_scheme'     => ['required', 'in:lunas,dp'],
            'addons'             => ['nullable', 'array'],
            'addons.*.addon_id'  => ['required', 'integer', 'exists:addons,id'],
            'addons.*.quantity'  => ['required', 'integer', 'min:1'],
        ];
    }
}
```

---

## 11. Controller — BookingController

### `app/Domains/Booking/Http/Controllers/Frontdoor/BookingController.php`

```php
<?php

namespace App\Domains\Booking\Http\Controllers\Frontdoor;

use App\Domains\Booking\DTOs\CheckoutData;
use App\Domains\Booking\Http\Requests\CheckoutRequest;
use App\Domains\Booking\Repositories\BookingRepository;
use App\Domains\Booking\Services\BookingService;
use App\Domains\MasterData\Models\Addon;
use App\Domains\MasterData\Models\Category;
use App\Domains\MasterData\Models\Package;
use App\Domains\MasterData\Models\PackageVariant;
use App\Domains\MasterData\Models\Schedule;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\View\View;

#[Middleware('auth', only: ['flow', 'checkout', 'success'])]
class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService    $bookingService,
        private readonly BookingRepository $repository,
    ) {}

    public function services(): View
    {
        $categories = Category::with(['packages' => function ($query) {
            $query->where('is_active', true)
                ->with(['variants' => fn ($q) => $q->where('is_active', true)->orderBy('price')]);
        }])->where('is_active', true)->get();

        return view('frontdoor.services.index', compact('categories'));
    }

    public function flow(Package $package): View
    {
        abort_if(! $package->is_active, 404);

        $variants = PackageVariant::with([
            'features',
            'backgrounds' => fn ($q) => $q->where('is_active', true),
        ])->where('package_id', $package->id)
          ->where('is_active', true)
          ->orderBy('price')
          ->get();

        $addons     = Addon::where('is_active', true)->orderBy('name')->get();
        $activeDays = Schedule::where('is_active', true)->pluck('day')->toArray();

        return view('frontdoor.booking.flow', compact('package', 'variants', 'addons', 'activeDays'));
    }

    public function getAvailableSlots(Request $request): JsonResponse
    {
        $request->validate(['date' => ['required', 'date']]);

        $date      = $request->date;
        $dayOfWeek = Carbon::parse($date)->dayOfWeekIso;

        $schedules     = Schedule::where('is_active', true)
            ->where('day', $dayOfWeek)
            ->orderBy('start_time')
            ->get(['start_time', 'end_time']);

        $occupiedSlots = $this->repository->getOccupiedSlotsByDate($date);

        $slots = $schedules->map(function ($schedule) use ($occupiedSlots) {
            $isOccupied = $occupiedSlots->some(fn ($b) =>
                $schedule->start_time < $b->end_time && $schedule->end_time > $b->start_time
            );

            return [
                'start_time'  => Carbon::parse($schedule->start_time)->format('H:i'),
                'end_time'    => Carbon::parse($schedule->end_time)->format('H:i'),
                'is_occupied' => $isOccupied,
            ];
        });

        return response()->json(['slots' => $slots]);
    }

    public function checkout(CheckoutRequest $request): JsonResponse
    {
        try {
            $data   = CheckoutData::from($request->validated());
            $result = $this->bookingService->processCheckout(auth()->user(), $data);

            return response()->json([
                'success'      => true,
                'snap_token'   => $result['snap_token'],
                'booking_code' => $result['booking_code'],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function success(string $bookingCode): View
    {
        $booking = $this->repository->findByCode($bookingCode);
        abort_if(! $booking || $booking->user_id !== auth()->id(), 404);

        return view('frontdoor.booking.success', compact('booking'));
    }
}
```

---

## 12. Controller — WebhookController

### `app/Domains/Booking/Http/Controllers/Frontdoor/WebhookController.php`

```php
<?php

namespace App\Domains\Booking\Http\Controllers\Frontdoor;

use App\Domains\Payment\Models\Payment;
use App\Http\Controllers\Controller;
use App\Jobs\SendWhatsappNotificationJob;
use Illuminate\Container\Attributes\Config;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WebhookController extends Controller
{
    public function __construct(
        #[Config('services.midtrans.server_key')] private string $serverKey,
    ) {}

    public function handle(Request $request): Response
    {
        $payload = $request->all();

        // Verifikasi SHA512 signature
        $calculatedSig = hash('sha512',
            $payload['order_id'] . $payload['status_code'] . $payload['gross_amount'] . $this->serverKey
        );

        if ($calculatedSig !== $payload['signature_key']) {
            activity()->log('Webhook signature mismatch: ' . ($payload['order_id'] ?? 'unknown'));
            return response('Forbidden', 403);
        }

        $payment = Payment::where('order_id', $payload['order_id'])->firstOrFail();
        $booking = $payment->booking()->with('user')->firstOrFail();

        $status      = $payload['transaction_status'];
        $fraudStatus = $payload['fraud_status'] ?? null;

        if (($status === 'capture' && $fraudStatus === 'accept') || $status === 'settlement') {
            $payment->update(['status' => 'Settlement', 'pay_date' => now()]);
            $booking->update(['status' => $payment->payment_purpose === 'dp' ? 'DP Terbayar' : 'Lunas']);
            SendWhatsappNotificationJob::dispatch(
                $booking->user->phone,
                $this->buildMessage($booking, $payment)
            );
        } elseif ($status === 'pending') {
            $payment->update(['status' => 'Pending']);
            $booking->update(['status' => 'Menunggu']);
        } elseif (in_array($status, ['deny', 'cancel', 'expire'])) {
            $payment->update(['status' => 'Batal']);
            $booking->update(['status' => 'Batal']);
        }

        return response('OK', 200);
    }

    private function buildMessage($booking, $payment): string
    {
        $appUrl = config('app.url');
        $code   = $booking->booking_code;

        if ($payment->payment_purpose === 'dp') {
            $amount = number_format($payment->amount, 0, ',', '.');
            return "Halo {$booking->user->name}, DP 60% sebesar Rp {$amount} untuk Kode Booking {$code} telah sah diterima.\n\nSisa 40% dilunasi di kasir studio. Lihat detail: {$appUrl}/dashboard";
        }

        $amount = number_format($payment->amount, 0, ',', '.');
        return "Halo {$booking->user->name}, pembayaran LUNAS PENUH Rp {$amount} untuk Kode Booking {$code} berhasil.\n\nJadwal terkunci aman. Sampai jumpa di studio!";
    }
}
```

---

## 13. Frontend — JS Modules

### `resources/js/features/booking/useState.js`

```js
export function createInitialState(variants, addons, activeDays) {
    return {
        currentStep: 1,
        isLoading: false,

        // Step 1
        allVariants: variants,
        selectedVariantId: null,
        selectedVariant: null,
        selectedBackgroundId: null,

        // Step 2
        activeDays,
        selectedDate: null,
        availableSlots: [],
        selectedSlot: null,
        isFetchingSlots: false,

        // Step 3
        allAddons: addons,
        selectedAddons: {}, // { addon_id: quantity }

        // Step 4
        paymentScheme: 'lunas',
        isProcessing: false,
        bookingCode: null,

        get totalPrice() {
            if (!this.selectedVariant) return 0;
            const addonTotal = Object.entries(this.selectedAddons).reduce((sum, [id, qty]) => {
                const addon = this.allAddons.find(a => a.id === parseInt(id));
                return sum + (addon ? addon.price * qty : 0);
            }, 0);
            return this.selectedVariant.price + addonTotal;
        },

        get grossAmount() {
            return this.paymentScheme === 'dp'
                ? Math.round(this.totalPrice * 0.60)
                : this.totalPrice;
        },

        get remainingAmount() {
            return this.totalPrice - this.grossAmount;
        },

        formatRupiah(amount) {
            return window.currency(amount, { symbol: 'Rp ', separator: '.', decimal: ',', precision: 0 }).format();
        },
    };
}
```

### `resources/js/features/booking/useCalendar.js`

```js
// Konversi ISO weekday (1=Sen...7=Min) ke Flatpickr (0=Min...6=Sab)
function isoToFlatpickr(days) {
    return days.map(d => d === 7 ? 0 : d);
}

let _timer = null;

export function calendarMixin(activeDays) {
    return {
        _fp: null,

        initCalendar() {
            const allowed = isoToFlatpickr(activeDays);
            this._fp = window.flatpickr(this.$refs.calendarInput, {
                inline: true,
                minDate: 'today',
                dateFormat: 'Y-m-d',
                disable: [(d) => !allowed.includes(d.getDay())],
                onChange: (_, dateStr) => {
                    this.selectedDate = dateStr;
                    this.selectedSlot = null;
                    this.debouncedFetch(dateStr);
                },
                locale: {
                    firstDayOfWeek: 1,
                    weekdays: {
                        shorthand: ['Min','Sen','Sel','Rab','Kam','Jum','Sab'],
                        longhand:  ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'],
                    },
                    months: {
                        shorthand: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'],
                        longhand:  ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'],
                    },
                },
            });
        },

        destroyCalendar() { this._fp?.destroy(); this._fp = null; },

        debouncedFetch(date) {
            clearTimeout(_timer);
            _timer = setTimeout(() => this.fetchSlots(date), 300);
        },

        async fetchSlots(date) {
            this.isFetchingSlots = true;
            this.availableSlots  = [];
            try {
                const res = await window.axios.get(window.routes.bookingSlots, { params: { date } });
                this.availableSlots = res.data.slots;
            } catch {
                window.Toast.fire({ icon: 'error', title: 'Gagal memuat jadwal tersedia.' });
            } finally {
                this.isFetchingSlots = false;
            }
        },
    };
}
```

### `resources/js/features/booking/useAddons.js`

```js
export function addonsMixin() {
    return {
        toggleAddon(id, hasQty) {
            const key = String(id);
            if (this.selectedAddons[key] !== undefined) {
                const next = { ...this.selectedAddons };
                delete next[key];
                this.selectedAddons = next;
            } else {
                this.selectedAddons = { ...this.selectedAddons, [key]: 1 };
            }
        },

        isAddonSelected: (id) => this.selectedAddons[String(id)] !== undefined,

        increment(id) {
            const k = String(id);
            if (this.selectedAddons[k]) {
                this.selectedAddons = { ...this.selectedAddons, [k]: this.selectedAddons[k] + 1 };
            }
        },

        decrement(id) {
            const k = String(id);
            if (this.selectedAddons[k] > 1) {
                this.selectedAddons = { ...this.selectedAddons, [k]: this.selectedAddons[k] - 1 };
            } else {
                this.toggleAddon(id, false);
            }
        },

        getQty: (id) => this.selectedAddons[String(id)] ?? 0,

        buildAddonsPayload() {
            return Object.entries(this.selectedAddons).map(([id, qty]) => ({
                addon_id: parseInt(id),
                quantity: qty,
            }));
        },
    };
}
```

### `resources/js/features/booking/useCheckout.js`

```js
export function checkoutMixin() {
    return {
        async triggerCheckout() {
            this.isProcessing = true;
            try {
                const res = await window.axios.post(window.routes.bookingCheckout, {
                    package_variant_id: this.selectedVariantId,
                    background_id:      this.selectedBackgroundId,
                    booking_date:       this.selectedDate,
                    start_time:         this.selectedSlot?.start_time,
                    payment_scheme:     this.paymentScheme,
                    addons:             this.buildAddonsPayload(),
                });

                if (!res.data.success) throw new Error(res.data.message || 'Checkout gagal.');

                this.bookingCode = res.data.booking_code;
                const code = this.bookingCode;

                window.snap.pay(res.data.snap_token, {
                    onSuccess: () => {
                        window.location.href = window.routes.bookingSuccess.replace(':code', code);
                    },
                    onPending: () => {
                        window.Toast.fire({ icon: 'info', title: 'Menunggu pembayaran diselesaikan.' });
                        this.isProcessing = false;
                    },
                    onError: () => {
                        window.Toast.fire({ icon: 'error', title: 'Pembayaran gagal. Silakan coba lagi.' });
                        this.isProcessing = false;
                    },
                    onClose: () => {
                        window.Toast.fire({ icon: 'warning', title: 'Pembayaran dibatalkan. Slot masih tersimpan.' });
                        this.isProcessing = false;
                    },
                });
            } catch (err) {
                const msg = err?.response?.data?.message || err.message || 'Terjadi kesalahan.';
                window.Toast.fire({ icon: 'error', title: msg });
                this.isProcessing = false;
            }
        },
    };
}
```

### `resources/js/features/booking/booking.js`

```js
import { createInitialState } from './useState.js';
import { calendarMixin }      from './useCalendar.js';
import { addonsMixin }        from './useAddons.js';
import { checkoutMixin }      from './useCheckout.js';

export function init(Alpine) {
    Alpine.data('bookingWizardHandler', (variants, addons, activeDays) => ({
        ...createInitialState(variants, addons, activeDays),
        ...calendarMixin(activeDays),
        ...addonsMixin(),
        ...checkoutMixin(),

        nextStep() {
            if (!this.canProceed()) return;
            this.currentStep++;
            if (this.currentStep === 2) {
                this.$nextTick(() => this.initCalendar());
            }
        },

        prevStep() {
            if (this.currentStep === 2) this.destroyCalendar();
            this.currentStep = Math.max(1, this.currentStep - 1);
        },

        canProceed() {
            if (this.currentStep === 1) return !!this.selectedVariantId;
            if (this.currentStep === 2) return !!this.selectedDate && !!this.selectedSlot;
            if (this.currentStep === 3) return true;
            return false;
        },

        selectVariant(variant) {
            this.selectedVariantId   = variant.id;
            this.selectedVariant     = variant;
            this.selectedBackgroundId = null;
        },

        selectSlot(slot) {
            if (!slot.is_occupied) this.selectedSlot = slot;
        },
    }));
}
```

---

## 14. Frontend — Blade Views

### `resources/views/frontdoor/services/index.blade.php`

```blade
<x-layouts.frontdoor title="Layanan Kami — Binary Photoworks">
    <section class="py-16 border-b border-[#E7E5E4]">
        <div class="max-w-6xl mx-auto px-6">
            <h1 class="font-['Libre_Baskerville'] text-4xl font-bold text-[#1C1917] tracking-tight">
                Layanan Studio
            </h1>
            <p class="mt-3 text-[#57534E] text-lg">
                Pilih paket yang sesuai dengan kebutuhan sesi foto Anda.
            </p>
        </div>
    </section>

    @foreach ($categories as $category)
        @if ($category->packages->isNotEmpty())
            <section class="py-12 border-b border-[#E7E5E4]">
                <div class="max-w-6xl mx-auto px-6">
                    <h2 class="font-['Libre_Baskerville'] text-2xl font-bold text-[#1C1917] mb-8">
                        {{ $category->name }}
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($category->packages as $package)
                            <x-frontdoor.booking.package-card :package="$package" />
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    @endforeach
</x-layouts.frontdoor>
```

### `resources/views/frontdoor/booking/flow.blade.php`

```blade
<x-layouts.frontdoor title="Booking Sesi Foto — {{ $package->name }}">
    <div
        x-data="bookingWizardHandler(
            {{ Js::from($variants) }},
            {{ Js::from($addons) }},
            {{ Js::from($activeDays) }}
        )"
        x-cloak
        class="min-h-screen bg-[#FAFAF9]"
    >
        {{-- Sticky Step Indicator --}}
        <div class="border-b border-[#E7E5E4] bg-white sticky top-[64px] z-40">
            <div class="max-w-5xl mx-auto px-6 py-4">
                <x-frontdoor.booking.step-indicator />
            </div>
        </div>

        <div class="max-w-5xl mx-auto px-6 py-10">
            <template x-if="currentStep === 1">
                @include('frontdoor.booking.steps.step-1-variant', ['package' => $package])
            </template>
            <template x-if="currentStep === 2">
                @include('frontdoor.booking.steps.step-2-schedule')
            </template>
            <template x-if="currentStep === 3">
                @include('frontdoor.booking.steps.step-3-addons')
            </template>
            <template x-if="currentStep === 4">
                @include('frontdoor.booking.steps.step-4-summary')
            </template>
        </div>
    </div>

    @include('frontdoor.booking.script')
</x-layouts.frontdoor>
```

### `resources/views/frontdoor/booking/script.blade.php`

```blade
@push('scripts')
<script>
    window.routes = {
        bookingSlots:    '{{ route('frontdoor.booking.api.slots') }}',
        bookingCheckout: '{{ route('frontdoor.booking.checkout') }}',
        bookingSuccess:  '{{ url('booking/success') }}/:code',
    };
</script>
@endpush
```

> **Layout frontdoor** — tambahkan conditional load Midtrans SDK di `<head>`:
> ```blade
> @if(request()->routeIs('frontdoor.booking.*'))
>     <script src="https://app.sandbox.midtrans.com/snap/snap.js"
>         data-client-key="{{ config('services.midtrans.client_key') }}">
>     </script>
> @endif
> ```
>
> Dan tambahkan `@stack('scripts')` sebelum `</body>`.

### `resources/views/frontdoor/booking/steps/step-1-variant.blade.php`

```blade
<div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
    {{-- Kiri: Gambar + Ketentuan --}}
    <div>
        <div class="aspect-[4/3] overflow-hidden bg-[#F5F5F4]">
            <img src="https://images.unsplash.com/photo-1542038784456-1ea8e935640e?w=800"
                alt="{{ $package->name }}" class="w-full h-full object-cover">
        </div>
        <div class="mt-6 border border-[#E7E5E4] p-5">
            <h3 class="font-semibold text-[#1C1917] text-sm uppercase tracking-widest mb-4">
                Ketentuan Paket
            </h3>
            <template x-if="selectedVariant && selectedVariant.features.length">
                <ul class="space-y-2">
                    <template x-for="f in selectedVariant.features" :key="f.id">
                        <li class="flex items-start gap-2 text-sm text-[#57534E]">
                            <i class="ri-check-line text-[#78716C] mt-0.5 shrink-0"></i>
                            <span x-text="f.description"></span>
                        </li>
                    </template>
                </ul>
            </template>
            <template x-if="!selectedVariant">
                <p class="text-sm text-[#A8A29E]">Pilih varian untuk melihat ketentuan.</p>
            </template>
        </div>
    </div>

    {{-- Kanan: Varian + Background --}}
    <div class="space-y-8">
        <div>
            <h3 class="font-['Libre_Baskerville'] text-xl font-bold text-[#1C1917] mb-4">Pilih Varian</h3>
            <div class="space-y-3">
                @foreach ($variants as $variant)
                    <x-frontdoor.booking.variant-radio :variant="$variant" />
                @endforeach
            </div>
        </div>

        <template x-if="selectedVariant && selectedVariant.backgrounds && selectedVariant.backgrounds.length > 0">
            <div>
                <h3 class="font-['Libre_Baskerville'] text-xl font-bold text-[#1C1917] mb-4">Pilih Background</h3>
                <div class="grid grid-cols-4 gap-3">
                    <template x-for="bg in selectedVariant.backgrounds" :key="bg.id">
                        <x-frontdoor.booking.background-thumb />
                    </template>
                </div>
            </div>
        </template>

        <div class="pt-4 border-t border-[#E7E5E4]">
            <x-shared.button variant="primary" class="w-full"
                x-bind:disabled="!selectedVariantId" x-on:click="nextStep()">
                Lanjutkan ke Jadwal Sesi
                <i class="ri-arrow-right-line ml-2"></i>
            </x-shared.button>
        </div>
    </div>
</div>
```

### `resources/views/frontdoor/booking/steps/step-2-schedule.blade.php`

```blade
<div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
    {{-- Kiri: Kalender Inline --}}
    <div>
        <h3 class="font-['Libre_Baskerville'] text-xl font-bold text-[#1C1917] mb-4">Pilih Tanggal</h3>
        <input type="text" x-ref="calendarInput" class="hidden">
        {{-- Flatpickr inline renders here --}}
    </div>

    {{-- Kanan: Slot Waktu --}}
    <div>
        <h3 class="font-['Libre_Baskerville'] text-xl font-bold text-[#1C1917] mb-4">Pilih Jam Sesi</h3>

        <template x-if="!selectedDate">
            <p class="text-sm text-[#A8A29E]">Pilih tanggal terlebih dahulu.</p>
        </template>

        <template x-if="isFetchingSlots">
            <div class="flex items-center gap-2 text-[#78716C] text-sm">
                <i class="ri-loader-4-line animate-spin"></i>
                <span>Memuat slot tersedia...</span>
            </div>
        </template>

        <template x-if="selectedDate && !isFetchingSlots">
            <div>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <template x-for="slot in availableSlots" :key="slot.start_time">
                        <x-frontdoor.booking.time-slot-button />
                    </template>
                </div>
                <template x-if="availableSlots.length === 0">
                    <p class="mt-4 text-sm text-[#A8A29E]">Tidak ada slot tersedia pada tanggal ini.</p>
                </template>
            </div>
        </template>

        <div class="mt-8 flex gap-3">
            <x-shared.button variant="ghost" x-on:click="prevStep()">
                <i class="ri-arrow-left-line mr-1"></i> Kembali
            </x-shared.button>
            <x-shared.button variant="primary" class="flex-1"
                x-bind:disabled="!selectedSlot" x-on:click="nextStep()">
                Lanjutkan ke Layanan Tambahan
                <i class="ri-arrow-right-line ml-2"></i>
            </x-shared.button>
        </div>
    </div>
</div>
```

### `resources/views/frontdoor/booking/steps/step-3-addons.blade.php`

```blade
<div>
    <h3 class="font-['Libre_Baskerville'] text-xl font-bold text-[#1C1917] mb-2">Layanan Tambahan</h3>
    <p class="text-sm text-[#57534E] mb-8">Opsional — bisa dikosongkan.</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <template x-for="addon in allAddons" :key="addon.id">
            <x-frontdoor.booking.addon-card />
        </template>
    </div>

    <div class="mt-10 flex gap-3">
        <x-shared.button variant="ghost" x-on:click="prevStep()">
            <i class="ri-arrow-left-line mr-1"></i> Kembali
        </x-shared.button>
        <x-shared.button variant="primary" class="flex-1" x-on:click="nextStep()">
            Lihat Ringkasan Pesanan
            <i class="ri-arrow-right-line ml-2"></i>
        </x-shared.button>
    </div>
</div>
```

### `resources/views/frontdoor/booking/steps/step-4-summary.blade.php`

```blade
<div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
    {{-- Invoice Table --}}
    <div class="lg:col-span-2 border border-[#E7E5E4]">
        <div class="border-b border-[#E7E5E4] p-5">
            <h3 class="font-['Libre_Baskerville'] text-xl font-bold text-[#1C1917]">Ringkasan Pesanan</h3>
        </div>
        <div class="p-5 space-y-3 text-sm">
            <div class="flex justify-between">
                <span class="text-[#57534E]">Paket</span>
                <span class="font-medium" x-text="selectedVariant?.name"></span>
            </div>
            <template x-if="selectedBackgroundId">
                <div class="flex justify-between">
                    <span class="text-[#57534E]">Background</span>
                    <span class="font-medium"
                        x-text="selectedVariant?.backgrounds?.find(b => b.id === selectedBackgroundId)?.name ?? '-'"></span>
                </div>
            </template>
            <div class="flex justify-between">
                <span class="text-[#57534E]">Tanggal Sesi</span>
                <span class="font-medium" x-text="selectedDate"></span>
            </div>
            <div class="flex justify-between">
                <span class="text-[#57534E]">Waktu Sesi</span>
                <span class="font-medium"
                    x-text="selectedSlot ? selectedSlot.start_time + ' – ' + selectedSlot.end_time + ' WITA' : '-'"></span>
            </div>
            <div class="border-t border-[#E7E5E4] pt-3 space-y-2">
                <div class="flex justify-between">
                    <span class="text-[#57534E]">Harga Varian</span>
                    <span x-text="formatRupiah(selectedVariant?.price ?? 0)"></span>
                </div>
                <template x-for="[addonId, qty] in Object.entries(selectedAddons)" :key="addonId">
                    <div class="flex justify-between">
                        <span class="text-[#57534E]"
                            x-text="(allAddons.find(a => a.id === parseInt(addonId))?.name ?? '') + ' ×' + qty"></span>
                        <span x-text="formatRupiah((allAddons.find(a => a.id === parseInt(addonId))?.price ?? 0) * qty)"></span>
                    </div>
                </template>
            </div>
            <div class="border-t border-[#E7E5E4] pt-3 flex justify-between font-bold text-base">
                <span class="text-[#1C1917]">Total</span>
                <span class="text-[#1C1917]" x-text="formatRupiah(totalPrice)"></span>
            </div>
        </div>
    </div>

    {{-- Payment Scheme + CTA --}}
    <div class="space-y-6">
        <div class="border border-[#E7E5E4] p-5">
            <h3 class="font-semibold text-[#1C1917] text-sm uppercase tracking-widest mb-5">Skema Pembayaran</h3>
            <div class="space-y-4">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="radio" x-model="paymentScheme" value="lunas" class="mt-0.5 accent-[#78716C]">
                    <div>
                        <span class="font-semibold text-[#1C1917] block">Lunas Penuh</span>
                        <span class="text-xs text-[#57534E]" x-text="formatRupiah(totalPrice)"></span>
                    </div>
                </label>
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="radio" x-model="paymentScheme" value="dp" class="mt-0.5 accent-[#78716C]">
                    <div>
                        <span class="font-semibold text-[#1C1917] block">DP 60%</span>
                        <span class="text-xs text-[#57534E]">
                            Bayar <span x-text="formatRupiah(grossAmount)"></span> sekarang,
                            sisa <span x-text="formatRupiah(remainingAmount)"></span> di kasir.
                        </span>
                    </div>
                </label>
            </div>
        </div>

        <div class="flex flex-col gap-3">
            <x-shared.button variant="ghost" x-on:click="prevStep()">
                <i class="ri-arrow-left-line mr-1"></i> Kembali
            </x-shared.button>
            <x-shared.button variant="primary" class="w-full"
                x-on:click="triggerCheckout()" x-bind:disabled="isProcessing">
                <template x-if="!isProcessing">
                    <span>Bayar Sekarang <i class="ri-secure-payment-line ml-1"></i></span>
                </template>
                <template x-if="isProcessing">
                    <span><i class="ri-loader-4-line animate-spin mr-1"></i> Mengunci Slot Jadwal...</span>
                </template>
            </x-shared.button>
        </div>
    </div>
</div>
```

### `resources/views/frontdoor/booking/success.blade.php`

```blade
<x-layouts.frontdoor title="Reservasi Berhasil — Binary Photoworks">
    <div class="max-w-2xl mx-auto px-6 py-20">
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-[#F0FDF4] border border-[#65A30D]/30 mb-6">
                <i class="ri-checkbox-circle-fill text-3xl text-[#65A30D]"></i>
            </div>
            <h1 class="font-['Libre_Baskerville'] text-3xl font-bold text-[#1C1917]">Reservasi Berhasil!</h1>
            <p class="mt-2 text-[#57534E]">Slot jadwal Anda telah terkunci aman di sistem kami.</p>
        </div>

        <div class="border border-[#E7E5E4]">
            <div class="p-5 border-b border-[#E7E5E4] flex items-center justify-between">
                <span class="text-sm text-[#57534E]">Kode Booking</span>
                <span class="font-mono font-bold text-[#1C1917] text-lg">{{ $booking->booking_code }}</span>
            </div>
            <div class="p-5 space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-[#57534E]">Paket</span>
                    <span class="font-medium">{{ $booking->packageVariant->name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[#57534E]">Tanggal Sesi</span>
                    <span class="font-medium">{{ $booking->booking_date->translatedFormat('d F Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[#57534E]">Waktu Sesi</span>
                    <span class="font-medium">
                        {{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} –
                        {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }} WITA
                    </span>
                </div>
                <div class="flex justify-between border-t border-[#E7E5E4] pt-3 font-bold text-base">
                    <span class="text-[#57534E]">Total Dibayar</span>
                    <span class="text-[#1C1917]">
                        Rp {{ number_format($booking->payments->first()?->amount ?? 0, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            @if ($booking->payment_scheme === 'dp')
                <div class="bg-[#FFFBEB] border-t border-[#CA8A04]/30 p-4">
                    <p class="text-xs text-[#92400E]">
                        <i class="ri-information-line mr-1"></i>
                        Sisa tagihan 40% (Rp {{ number_format($booking->total_price - ($booking->payments->first()?->amount ?? 0), 0, ',', '.') }})
                        dilunasi di kasir studio pada hari sesi foto.
                    </p>
                </div>
            @endif
        </div>

        <div class="mt-8 flex flex-col sm:flex-row gap-3">
            <x-shared.button as="a" href="{{ route('frontdoor.dashboard.index') }}" variant="outline" class="flex-1 text-center">
                <i class="ri-calendar-check-line mr-2"></i> Lihat Riwayat Pesanan
            </x-shared.button>
            <x-shared.button variant="primary" class="flex-1">
                <i class="ri-download-2-line mr-2"></i> Unduh Bukti Reservasi
            </x-shared.button>
        </div>
    </div>
</x-layouts.frontdoor>
```

---

## 15. Blade Components

### `resources/views/components/frontdoor/booking/package-card.blade.php`

```blade
@props(['package'])
@php $minPrice = $package->variants->min('price'); @endphp

<div class="border border-[#E7E5E4] bg-white flex flex-col">
    <div class="aspect-[16/9] overflow-hidden bg-[#F5F5F4]">
        <img src="https://images.unsplash.com/photo-1513364776144-60967b0f800f?w=600"
            alt="{{ $package->name }}" class="w-full h-full object-cover">
    </div>
    <div class="p-5 flex flex-col flex-1">
        <span class="text-[11px] font-semibold text-[#78716C] uppercase tracking-widest mb-2">
            {{ $package->category->name ?? '' }}
        </span>
        <h3 class="font-['Libre_Baskerville'] text-xl font-bold text-[#1C1917]">{{ $package->name }}</h3>
        <p class="mt-1 text-sm text-[#57534E]">
            Mulai dari <span class="font-semibold text-[#1C1917]">Rp {{ number_format($minPrice, 0, ',', '.') }}</span>
        </p>
        <div class="mt-auto pt-5">
            <x-shared.button as="a" href="{{ route('frontdoor.booking.flow', $package->slug) }}"
                variant="primary" class="w-full text-center">
                Pilih Layanan <i class="ri-arrow-right-line ml-1"></i>
            </x-shared.button>
        </div>
    </div>
</div>
```

### `resources/views/components/frontdoor/booking/variant-radio.blade.php`

```blade
@props(['variant'])

<label class="block border cursor-pointer transition-colors"
    x-bind:class="selectedVariantId === {{ $variant->id }}
        ? 'border-[#78716C] bg-[#F5F5F4]'
        : 'border-[#E7E5E4] bg-white hover:border-[#D6D3D1]'"
    x-on:click="selectVariant({{ Js::from($variant->load('features', 'backgrounds')) }})">
    <div class="p-4 flex items-center gap-4">
        <div class="shrink-0 w-4 h-4 border border-[#A8A29E] rounded-full flex items-center justify-center"
            x-bind:class="selectedVariantId === {{ $variant->id }} ? 'border-[#78716C]' : ''">
            <div class="w-2 h-2 rounded-full bg-[#78716C]" x-show="selectedVariantId === {{ $variant->id }}" x-transition></div>
        </div>
        <div class="flex-1 min-w-0">
            <span class="block font-semibold text-[#1C1917]">{{ $variant->name }}</span>
            <span class="text-xs text-[#57534E]">{{ $variant->duration }} menit</span>
        </div>
        <span class="font-bold text-[#1C1917] shrink-0">Rp {{ number_format($variant->price, 0, ',', '.') }}</span>
    </div>
</label>
```

### `resources/views/components/frontdoor/booking/background-thumb.blade.php`

```blade
{{-- Digunakan di dalam x-for="bg in selectedVariant.backgrounds" --}}
<div class="flex flex-col items-center gap-2 cursor-pointer" x-on:click="selectedBackgroundId = bg.id">
    <div class="w-16 h-16 border-2 overflow-hidden transition-colors"
        x-bind:class="selectedBackgroundId === bg.id ? 'border-[#78716C]' : 'border-[#E7E5E4]'">
        <img src="https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=200"
            x-bind:alt="bg.name" class="w-full h-full object-cover">
    </div>
    <span class="text-[11px] text-[#57534E] text-center line-clamp-1" x-text="bg.name"></span>
</div>
```

### `resources/views/components/frontdoor/booking/time-slot-button.blade.php`

```blade
{{-- Digunakan di dalam x-for="slot in availableSlots" --}}
<button type="button" class="border py-3 text-sm font-medium transition-colors text-center"
    x-bind:class="{
        'border-[#78716C] bg-[#F5F5F4] text-[#1C1917]': selectedSlot?.start_time === slot.start_time && !slot.is_occupied,
        'border-[#E7E5E4] bg-[#F5F5F4] text-[#A8A29E] cursor-not-allowed line-through': slot.is_occupied,
        'border-[#E7E5E4] bg-white text-[#57534E] hover:border-[#D6D3D1]': !slot.is_occupied && selectedSlot?.start_time !== slot.start_time,
    }"
    x-bind:disabled="slot.is_occupied"
    x-on:click="selectSlot(slot)"
    x-text="slot.start_time">
</button>
```

### `resources/views/components/frontdoor/booking/addon-card.blade.php`

```blade
{{-- Digunakan di dalam x-for="addon in allAddons" --}}
<div class="border p-4 transition-colors"
    x-bind:class="isAddonSelected(addon.id) ? 'border-[#78716C] bg-[#F5F5F4]' : 'border-[#E7E5E4] bg-white'">
    <div class="flex items-start justify-between gap-3">
        <div class="flex-1 min-w-0">
            <span class="font-semibold text-[#1C1917] block" x-text="addon.name"></span>
            <span class="text-xs text-[#57534E]"
                x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(addon.price)"></span>
        </div>

        <template x-if="addon.has_quantity && isAddonSelected(addon.id)">
            <div class="flex items-center border border-[#E7E5E4] shrink-0">
                <button type="button" class="px-2 py-1 text-[#78716C] hover:bg-[#F5F5F4] text-lg leading-none"
                    x-on:click="decrement(addon.id)">−</button>
                <span class="px-3 text-sm font-medium" x-text="getQty(addon.id)"></span>
                <button type="button" class="px-2 py-1 text-[#78716C] hover:bg-[#F5F5F4] text-lg leading-none"
                    x-on:click="increment(addon.id)">+</button>
            </div>
        </template>

        <template x-if="!addon.has_quantity || !isAddonSelected(addon.id)">
            <button type="button"
                class="shrink-0 w-6 h-6 border flex items-center justify-center transition-colors"
                x-bind:class="isAddonSelected(addon.id) ? 'border-[#78716C] bg-[#78716C]' : 'border-[#D6D3D1] bg-white'"
                x-on:click="toggleAddon(addon.id, addon.has_quantity)">
                <i class="ri-check-line text-white text-sm" x-show="isAddonSelected(addon.id)"></i>
            </button>
        </template>
    </div>

    <template x-if="!isAddonSelected(addon.id)">
        <button type="button" class="w-full text-left mt-3 text-xs text-[#78716C] font-medium"
            x-on:click="toggleAddon(addon.id, addon.has_quantity)">
            + Tambahkan
        </button>
    </template>
</div>
```

### `resources/views/components/frontdoor/booking/step-indicator.blade.php`

```blade
@php
    $steps = [1 => 'Pilih Paket', 2 => 'Jadwal', 3 => 'Add-ons', 4 => 'Ringkasan'];
@endphp
<div class="flex items-center" x-cloak>
    @foreach ($steps as $num => $label)
        <div class="flex items-center">
            <div class="flex items-center gap-2"
                x-bind:class="{{ $num }} <= currentStep ? 'text-[#1C1917]' : 'text-[#A8A29E]'">
                <div class="w-7 h-7 border flex items-center justify-center text-sm font-bold shrink-0"
                    x-bind:class="
                        {{ $num }} === currentStep
                            ? 'border-[#1C1917] bg-[#1C1917] text-white'
                            : ({{ $num }} < currentStep
                                ? 'border-[#78716C] bg-[#F5F5F4] text-[#78716C]'
                                : 'border-[#E7E5E4] bg-white text-[#A8A29E]')
                    ">
                    <template x-if="{{ $num }} < currentStep"><i class="ri-check-line text-xs"></i></template>
                    <template x-if="{{ $num }} >= currentStep"><span>{{ $num }}</span></template>
                </div>
                <span class="text-sm font-medium hidden sm:block">{{ $label }}</span>
            </div>
            @if ($num < count($steps))
                <div class="w-8 sm:w-12 h-px mx-2"
                    x-bind:class="{{ $num }} < currentStep ? 'bg-[#78716C]' : 'bg-[#E7E5E4]'"></div>
            @endif
        </div>
    @endforeach
</div>
```

---

## 16. Registrasi & Catatan Implementasi

### `resources/js/app.js` — Tambahkan import booking

```js
import { init as initBooking } from './features/booking/booking.js';

// Setelah semua Alpine.plugin:
initBooking(Alpine);
```

### Tabel Keputusan Implementasi

| Topik | Keputusan |
|---|---|
| **Gambar paket & background** | Gunakan link Unsplash statis (photo-1542038784456, photo-1513364776144, dll) hingga fitur upload admin tersedia. |
| **Flatpickr attach point** | Attach ke `x-ref="calendarInput"` (input hidden), `inline: true` agar render di tempat. Panggil `initCalendar()` via `$nextTick` saat masuk Step 2. Destroy saat keluar. |
| **Debounce slot fetch** | 300ms via `clearTimeout` / `setTimeout`. Menjaga performa query PostgreSQL. |
| **Double-booking guard** | 2 lapis: frontend (slot disabled) + server final validation dalam `BookingRepository::isSlotOccupied()` di dalam DB transaction. |
| **Booking Code** | `BPW-{category_code}{variant_id padded 2}-{YYMMDD}-{RAND 3 uppercase}`. Sesuai BPW Standard. |
| **DP gross amount** | `round(total_price * 0.60)` untuk menghindari pecahan. |
| **Midtrans SDK** | Load conditional hanya di route `frontdoor.booking.*`. Gunakan sandbox URL untuk development. |
| **WhatsApp notif** | Dispatch via `SendWhatsappNotificationJob` (queue, `#[Tries(3)]` `#[Timeout(60)]`) untuk non-blocking response. |
| **CSRF Webhook** | Kecualikan `/api/payments/webhook` dari CSRF middleware via `bootstrap/app.php`. |
| **Variants features di Step 1** | Pastikan `PackageVariant::with('features', 'backgrounds')` diload di `BookingController::flow()` agar tersedia di Alpine state. |
