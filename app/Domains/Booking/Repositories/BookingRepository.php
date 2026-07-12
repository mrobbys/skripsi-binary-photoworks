<?php

namespace App\Domains\Booking\Repositories;

use App\Domains\Booking\DTOs\BookingData;
use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Models\Booking;
use App\Domains\Payment\Enums\PaymentStatus;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BookingRepository
{
    /**
     * Query untuk cek apakah slot sudah terisi
     * Algoritma overlap: start_a < end_b AND end_a > start_b
     * @param string $date
     * @param string $startTime
     * @param string $endTime
     */
    public function isSlotOccupied(string $date, string $startTime, string $endTime): bool
    {
        $query = Booking::where('booking_date', $date)
            ->where(
                fn($q) => $q
                    ->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime)
            );

        return $this->applyActiveSlotFilter($query)->exists();
    }

    /**
     * Ambil data slot yang sudah terisi berdasarkan tanggal
     * @param string $date
     */
    public function getOccupiedSlotsByDate(string $date): Collection
    {
        $query = Booking::select('start_time', 'end_time')
            ->where('booking_date', $date);

        return $this->applyActiveSlotFilter($query)->get();
    }

    /**
     * Create data booking
     * @param BookingData $data
     */
    public function create(BookingData $data): Booking
    {
        return Booking::create($data->toArray());
    }

    /**
     * Cari data booking berdasarkan code
     * @param string $code
     */
    public function findByCode(string $code): ?Booking
    {
        return Booking::with(['user', 'packageVariant.package', 'background', 'addons', 'payments'])
            ->where('booking_code', $code)
            ->first();
    }

    /**
     * Ambil semua data booking dari user tertentu (untuk dashboard user)
     * @param int $userId
     */
    public function getByUser(int $userId): Collection
    {
        return Booking::with(['packageVariant.package', 'background'])
            ->where('user_id', $userId)
            ->orderBy('booking_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->get();
    }

    /**
     * Cari booking berdasarkan booking_code milik user tertentu
     * @param string $bookingCode
     * @param int    $userId
     */
    public function findByCodeAndUser(string $bookingCode, int $userId): ?Booking
    {
        return Booking::with(['packageVariant.package', 'background', 'payments'])
            ->where('booking_code', $bookingCode)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Ambil data booking secara paginasi berdasarkan tab status
     * @param int $userId
     * @param string $tab
     * @param int $limit
     */
    public function getPaginatedByUser(int $userId, string $tab, int $limit = 5)
    {
        $query = Booking::with(['packageVariant.package', 'background', 'payments'])
            ->where('user_id', $userId);

        if ($tab === 'upcoming') {
            $query->whereIn('status', [
                BookingStatus::PENDING,
                BookingStatus::DP_PAID,
                BookingStatus::SUCCESS
            ]);
        } else {
            $query->whereIn('status', [
                BookingStatus::DONE,
                BookingStatus::CANCELLED
            ]);
        }

        return $query->orderBy('created_at', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate($limit);
    }

    /**
     * Cek apakah slot sudah terisi, kecuali booking milik booking_id tertentu.
     * Digunakan saat reschedule agar slot lama milik user sendiri tidak dianggap bentrok.
     * @param string $date
     * @param string $startTime
     * @param string $endTime
     * @param int $excludeBookingId ID booking yang sedang di-reschedule
     */
    public function isSlotOccupiedExcluding(string $date, string $startTime, string $endTime, int $excludeBookingId): bool
    {
        $query = Booking::where('booking_date', $date)
            ->where('id', '!=', $excludeBookingId)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime);
            });

        return $this->applyActiveSlotFilter($query)->exists();
    }

    /**
     * Helper untuk filter slot yang benar-benar aktif (mengabaikan Cancelled & Pending Expired)
     * @param object $query
     */
    private function applyActiveSlotFilter(object $query)
    {
        $now = Carbon::now();

        return $query->where('status', '!=', BookingStatus::CANCELLED)
            ->where(
                fn($q) => $q
                    // Jika statusnya bukan pending, maka aman (jangan dibuang)
                    ->where('status', '!=', BookingStatus::PENDING)
                    // Jika pending, pastikan tidak ada tagihan pembayaran yang expired
                    ->orWhereDoesntHave(
                        'payments',
                        fn($pq) => $pq
                            ->where('status', PaymentStatus::PENDING)
                            ->where('snap_token_expiry', '<', $now)
                    )
            );
    }
}
