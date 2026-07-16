<?php

namespace App\Domains\Booking\Http\Controllers\Backdoor;

use App\Domains\Booking\DTOs\ManageBookingIndexData;
use App\Domains\Booking\DTOs\ManualBookingData;
use App\Domains\Booking\DTOs\UpsellAddonData;
use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Http\Requests\StoreManualBookingRequest;
use App\Domains\Booking\Http\Requests\UpsellAddonRequest;
use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\Services\CancelBookingService;
use App\Domains\Booking\Services\CreateManualBookingService;
use App\Domains\Booking\Services\SettleBookingService;
use App\Domains\MasterData\Models\Addon;
use App\Domains\MasterData\Models\Background;
use App\Domains\MasterData\Models\Package;
use App\Domains\Payment\Enums\PaymentStatus;
use App\Domains\Payment\Models\Payment;
use App\Domains\User\Models\User;
use App\Http\Controllers\Controller;
use App\Jobs\SendWhatsappNotificationJob;
use App\Support\Formatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ManageBookingController extends Controller
{
    public function __construct(
        private readonly CreateManualBookingService $createService,
        private readonly SettleBookingService $settleService,
        private readonly CancelBookingService $cancelBookingService
    ) {}

    /**
     * Halaman Index: Widget statistik
     */
    public function index(Request $request): View|JsonResponse
    {
        // Jika request dari DataTables AJAX (search)
        try {
            if ($request->wantsJson()) {
                $query = Booking::with(['user', 'packageVariant.package', 'payments'])
                    ->whereIn('status', [
                        BookingStatus::PENDING,
                        BookingStatus::DP_PAID,
                        BookingStatus::SUCCESS,
                        BookingStatus::CANCELLED,
                        BookingStatus::DONE,
                    ])
                    ->latest();

                if ($search = $request->input('search')) {
                    $query->where(function ($q) use ($search) {
                        $q->where('booking_code', 'ilike', "%{$search}%")
                            ->orWhereHas('user', fn($u) => $u->where('name', 'ilike', "%{$search}%"))
                            ->orWhereHas('user', fn($u) => $u->where('phone', 'ilike', "%{$search}%"));
                    });
                }

                $limit = max(1, min((int) $request->query('limit', 10), 100));
                $bookings = $query->paginate($limit);

                $stats = [
                    'total_revenue' => Payment::where('status', PaymentStatus::SETTLEMENT)->sum('amount'),
                    'count_success' => Booking::where('status', BookingStatus::SUCCESS)->count(),
                    'count_dp_paid' => Booking::where('status', BookingStatus::DP_PAID)->count(),
                ];

                return response()->json(array_merge([
                    'success' => true,
                    'data' => ManageBookingIndexData::collect($bookings->items()),
                    'current_page' => $bookings->currentPage(),
                    'last_page' => $bookings->lastPage(),
                    'total' => $bookings->total(),
                ], $stats));
            }
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }

        return view('backdoor.booking-management.index');
    }

    /**
     * Ambil data user berdasarkan pencarian
     */
    public function searchUsers(Request $request): JsonResponse
    {
        $term = $request->query('q');

        $users = User::select(['id', 'name', 'email', 'phone'])
            ->when($term, function ($q) use ($term) {
                $q->where('name', 'ilike', "%{$term}%")
                    ->orWhere('email', 'ilike', "%{$term}%")
                    ->orWhere('phone', 'ilike', "%{$term}%");
            })
            ->limit(20)
            ->get();

        return response()->json($users);
    }

    /**
     * Halaman Create: Form pembuatan booking manual.
     */
    public function create(): View
    {
        $users = User::orderBy('name')->get(['id', 'name', 'phone', 'email']);
        $packages = Package::where('is_active', true)
            ->with(['variants' => function ($query) {
                $query->where('is_active', true)
                    ->where('is_whatsapp_only', false);
            }])
            ->whereHas('variants', function ($query) {
                $query->where('is_active', true)
                    ->where('is_whatsapp_only', false);
            })
            ->orderBy('name')
            ->get();

        $backgrounds = Background::where('is_active', true)->orderBy('name')->get();
        $addons = Addon::where('is_active', true)->orderBy('name')->get();

        return view('backdoor.booking-management.create', compact(
            'users',
            'packages',
            'backgrounds',
            'addons'
        ));
    }

    /**
     * Simpan booking manual baru.
     */
    public function store(StoreManualBookingRequest $request): JsonResponse
    {
        try {
            $booking = $this->createService->execute(ManualBookingData::fromRequest($request));

            return response()->json([
                'success' => true,
                'message' => "Booking {$booking->booking_code} berhasil dibuat.",
                'data' => $booking,
            ], 201);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Halaman Detail: Ringkasan booking (read-only) + penambahan data add-ons.
     */
    public function show(Request $request, Booking $booking): JsonResponse|View
    {
        $booking->load([
            'user',
            'packageVariant.package',
            'background',
            'addons',
            'payments',
        ]);

        // jika request ajax
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $booking,
            ]);
        }

        $availableAddons = Addon::where('is_active', true)->orderBy('name')->get();

        return view('backdoor.booking-management.show', compact(
            'booking',
            'availableAddons'
        ));
    }

    /**
     * Update status booking menjadi lunas.
     */
    public function settle(Booking $booking): JsonResponse
    {
        try {
            $this->settleService->execute($booking);

            return response()->json([
                'success' => true,
                'message' => "Booking {$booking->booking_code} berhasil dilunasi.",
                'data' => $booking,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan server.'], 500);
        }
    }

    /**
     * Simpan link Google Drive hasil edit foto.
     */
    public function updateGdrive(Request $request, Booking $booking): JsonResponse
    {
        $validated = $request->validate([
            'gdrive_link' => ['required', 'url', 'starts_with:http://,https://'],
        ], [
            'gdrive_link.required' => 'Link Google Drive wajib diisi.',
            'gdrive_link.url' => 'Format link tidak valid.',
            'gdrive_link.starts_with' => 'Link harus diawali dengan http:// atau https://',
        ]);

        try {
            $booking->update(['gdrive_link' => $validated['gdrive_link'], 'status' => BookingStatus::DONE]);

            $isSendWa = $request->boolean('send_wa_notification', true);

            if ($isSendWa) {
                // Kirim notifikasi WA via Fonnte Service
                SendWhatsappNotificationJob::dispatch(
                    $booking->user->phone,
                    $this->buildMessage($booking)
                );
            }

            return response()->json([
                'success' => true,
                'message' => "Link GDrive berhasil disimpan." . ($isSendWa ? " Notifikasi WA dikirim ke {$booking->user->phone}." : ""),
                'data' => $booking,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
            ], 500);
        }
    }

    /**
     * Batalkan booking.
     */
    public function cancel(Booking $booking): JsonResponse
    {
        try {
            $this->cancelBookingService->execute(
                bookingCode: $booking->booking_code,
                userId: $booking->user_id,
                isAdmin: true
            );

            return response()->json([
                'success' => true,
                'message' => "Booking {$booking->booking_code} berhasil dibatalkan.",
                'data' => $booking,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan. Silahkan coba lagi.',
            ], 500);
        }
    }

    /**
     * Tambah addon ke booking yang sudah ada (upsell di halaman detail).
     */
    public function upsellAddon(UpsellAddonRequest $request, Booking $booking): JsonResponse
    {
        $dto = UpsellAddonData::fromRequest($request);
        $addon = Addon::findOrFail($dto->addon_id);

        DB::transaction(function () use ($booking, $addon, $dto) {
            // Attach atau update pivot (jika addon sama sudah ada, qty bertambah)
            $existingPivot = $booking->addons()->where('addon_id', $addon->id)->first();

            if ($existingPivot) {
                $booking->addons()->updateExistingPivot($addon->id, [
                    'quantity' => $existingPivot->pivot->quantity + $dto->quantity,
                ]);
            } else {
                $booking->addons()->attach($addon->id, [
                    'quantity' => $dto->quantity,
                    'price_at_purchase' => $addon->price,
                ]);
            }

            // Update total_price booking
            $addonSubtotal = $addon->price * $dto->quantity;
            $booking->increment('total_price', $addonSubtotal);
        });

        $booking->refresh()->load([
            'user',
            'packageVariant.package',
            'background',
            'addons',
            'payments',
        ]);

        return response()->json([
            'success' => true,
            'message' => "Layanan \"{$addon->name}\" berhasil ditambahkan ke tagihan.",
            'data' => $booking,
        ]);
    }

    /**
     * Buat pesan untuk notifikasi whatsapp fonnte
     */
    private function buildMessage(Booking $booking): string
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

Berikut adalah Link Goggle Drive untuk mengunduh hasil foto Anda:
{$gdriveLink}

Terima kasih telah mempercayakan momen berharga Anda kepada kami!
TEXT;
    }
}
