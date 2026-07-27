<?php

namespace App\Domains\Booking\Http\Controllers\Backdoor;

use App\Domains\Booking\DTOs\ManualBookingData;
use App\Domains\Booking\DTOs\UpsellAddonData;
use App\Domains\Booking\Http\Requests\StoreManualBookingRequest;
use App\Domains\Booking\Http\Requests\UpdateGdriveRequest;
use App\Domains\Booking\Http\Requests\UpsellAddonRequest;
use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\Services\CancelBookingService;
use App\Domains\Booking\Services\CreateManualBookingService;
use App\Domains\Booking\Services\FetchManageBookingDataService;
use App\Domains\Booking\Services\RefundBookingOverpaymentService;
use App\Domains\Booking\Services\RemoveBookingAddonService;
use App\Domains\Booking\Services\SettleBookingService;
use App\Domains\Booking\Services\UpdateBookingGdriveService;
use App\Domains\Booking\Services\UpsellBookingAddonService;
use App\Domains\MasterData\Models\Addon;
use App\Domains\MasterData\Models\Background;
use App\Domains\MasterData\Models\Package;
use App\Domains\User\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\View\View;

#[Middleware('permission:booking-management-view', only: ['index', 'data', 'show', 'showData'])]
#[Middleware('permission:booking-management-create', only: ['create', 'store'])]
#[Middleware('permission:booking-management-update', only: ['settle', 'updateGdrive', 'upsellAddon', 'removeAddon', 'cancel', 'refund'])]
class ManageBookingController extends Controller
{
    public function __construct(
        private readonly CreateManualBookingService $createService,
        private readonly SettleBookingService $settleService,
        private readonly CancelBookingService $cancelBookingService,
        private readonly UpdateBookingGdriveService $updateGdriveService,
        private readonly FetchManageBookingDataService $fetchDataService,
        private readonly UpsellBookingAddonService $upsellService,
        private readonly RemoveBookingAddonService $removeService,
        private readonly RefundBookingOverpaymentService $refundService
    ) {}

    /**
     * Halaman Index: Widget statistik
     */
    public function index(): View
    {
        return view('backdoor.booking-management.index');
    }

    /**
     * Ambil data booking untuk DataTables
     * @param Request $request
     */
    public function data(Request $request): JsonResponse
    {
        return response()->json($this->fetchDataService->execute($request));
    }

    /**
     * Halaman Create: Form pembuatan booking manual.
     */
    public function create(): View
    {
        $users = User::select(['id', 'name', 'phone', 'email'])
            ->Role('user')
            ->orderBy('name')
            ->get();

        $packages = Package::select(['id', 'name'])
            ->where('is_active', true)
            ->with(['variants' => function ($query) {
                $query->select(['id', 'package_id', 'name', 'duration', 'price'])
                    ->where('is_active', true)
                    ->where('is_whatsapp_only', false);
            }])
            ->whereHas('variants', function ($query) {
                $query->where('is_active', true)
                    ->where('is_whatsapp_only', false);
            })
            ->orderBy('name')
            ->get();

        $backgrounds = Background::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
        $addons = Addon::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'has_quantity']);

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

            return $this->successResponse("Booking {$booking->booking_code} berhasil dibuat.", $booking, 201);
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Halaman Detail: Ringkasan booking (read-only) + penambahan data add-ons.
     */
    public function show(Booking $booking): View
    {
        $availableAddons = Addon::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'has_quantity']);

        return view('backdoor.booking-management.show', compact(
            'booking',
            'availableAddons'
        ));
    }

    /**
     * Ambil data detail booking (JSON) untuk AJAX
     */
    public function showData(Booking $booking): JsonResponse
    {
        $booking->load([
            'user:id,name,phone,email',
            'packageVariant:id,name,package_id',
            'packageVariant.package:id,name',
            'background:id,name',
            'addons:id,name,price,has_quantity',
            'payments:id,booking_id,order_id,amount,status,payment_type,payment_purpose,pay_date',
        ]);

        $totalPaidRaw = $booking->payments
            ->where('status', \App\Domains\Payment\Enums\PaymentStatus::SETTLEMENT->value)
            ->where('payment_purpose', '!=', \App\Domains\Payment\Enums\PaymentPurpose::REFUND->value)
            ->sum('amount');

        $totalRefunded = $booking->payments
            ->where('status', \App\Domains\Payment\Enums\PaymentStatus::SETTLEMENT->value)
            ->where('payment_purpose', \App\Domains\Payment\Enums\PaymentPurpose::REFUND->value)
            ->sum('amount');

        $overpayment = max(0, $totalPaidRaw - $booking->total_price - $totalRefunded);

        return $this->successResponse('Berhasil mengambil data.', [
            'booking' => $booking,
            'summary' => [
                'total_paid_raw' => $totalPaidRaw,
                'total_refunded' => $totalRefunded,
                'net_paid' => $totalPaidRaw - $totalRefunded,
                'overpayment' => $overpayment,
                'has_overpayment' => $totalPaidRaw > $booking->total_price,
            ],
        ]);
    }

    /**
     * Update status booking menjadi lunas.
     */
    public function settle(Booking $booking): JsonResponse
    {
        try {
            $this->settleService->execute($booking);

            return $this->successResponse("Booking {$booking->booking_code} berhasil dilunasi.");
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan server.');
        }
    }

    /**
     * Simpan link Google Drive hasil edit foto.
     */
    public function updateGdrive(UpdateGdriveRequest $request, Booking $booking): JsonResponse
    {
        try {
            $isSendWa = $request->boolean('send_wa_notification');

            $this->updateGdriveService->execute(
                $booking,
                $request->validated('gdrive_link'),
                $isSendWa
            );

            return $this->successResponse(
                "Link GDrive berhasil disimpan." . ($isSendWa && $booking->user?->phone ? " Notifikasi WA dikirim ke {$booking->user->phone}." : "")
            );
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan server.');
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

            return $this->successResponse("Booking {$booking->booking_code} berhasil dibatalkan.");
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan. Silahkan coba lagi.');
        }
    }

    /**
     * Tambah addon ke booking yang sudah ada (upsell di halaman detail).
     */
    public function upsellAddon(UpsellAddonRequest $request, Booking $booking): JsonResponse
    {
        try {
            $this->upsellService->execute(
                $booking,
                UpsellAddonData::fromRequest($request)
            );

            return $this->successResponse("Layanan tambahan berhasil ditambahkan ke tagihan.");
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan server.');
        }
    }

    /**
     * Hapus addon dari booking (di halaman detail).
     */
    public function removeAddon(Booking $booking, Addon $addon): JsonResponse
    {
        try {
            $this->removeService->execute($booking, $addon);

            return $this->successResponse('Layanan tambahan berhasil dihapus.');
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan server.');
        }
    }

    /**
     * Catat refund kelebihan pembayaran.
     */
    public function refund(Booking $booking): JsonResponse
    {
        try {
            $this->refundService->execute($booking);

            return $this->successResponse('Refund kelebihan bayar berhasil dicatat.');
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan server.');
        }
    }
}
