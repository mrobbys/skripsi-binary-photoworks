<?php

namespace App\Domains\Booking\Services;

use App\Domains\Booking\DTOs\ManageBookingIndexData;
use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Models\Booking;
use App\Domains\Payment\Enums\PaymentStatus;
use App\Domains\Payment\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class FetchManageBookingDataService
{
    /**
     * Ambil data booking untuk DataTables beserta statistik revenue & booking count
     */
    public function execute(Request $request): array
    {
        $query = Booking::with([
            'user:id,name',
            'packageVariant:id,name,package_id',
            'packageVariant.package:id,name',
        ])
        ->select([
            'id',
            'user_id',
            'package_variant_id',
            'booking_code',
            'booking_date',
            'start_time',
            'end_time',
            'status',
            'total_price',
            'payment_scheme',
        ])
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

        $statusCounts = Booking::selectRaw("
            COUNT(CASE WHEN status = ? THEN 1 END) as count_success,
            COUNT(CASE WHEN status = ? THEN 1 END) as count_dp_paid
        ", [BookingStatus::SUCCESS->value, BookingStatus::DP_PAID->value])->first();

        $stats = [
            'total_revenue' => Cache::remember('stats.total_revenue', 60, fn() => Payment::where('status', PaymentStatus::SETTLEMENT)->sum('amount')),
            'count_success' => (int) ($statusCounts->count_success ?? 0),
            'count_dp_paid' => (int) ($statusCounts->count_dp_paid ?? 0),
        ];

        return array_merge([
            'success' => true,
            'data' => ManageBookingIndexData::collect($bookings->items()),
            'current_page' => $bookings->currentPage(),
            'last_page' => $bookings->lastPage(),
            'total' => $bookings->total(),
        ], $stats);
    }
}
