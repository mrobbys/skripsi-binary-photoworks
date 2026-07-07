<?php

namespace App\Domains\Booking\Repositories;

use App\Domains\Booking\DTOs\BookingData;
use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Models\Booking;
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
        return Booking::where('booking_date', $date)
            ->where('status', '!=', BookingStatus::CANCELLED)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime)
                        ->where('end_time', '>', $startTime);
                });
            })->exists();
    }

    /**
     * Ambil data slot yang sudah terisi berdasarkan tanggal
     * @param string $date
     */
    public function getOccupiedSlotsByDate(string $date): Collection
    {
        return Booking::select('start_time', 'end_time')
            ->where('booking_date', $date)
            ->where('status', '!=', BookingStatus::CANCELLED)
            ->get();
    }

    /**
     * Create data booking
     * @param BookingData $data
     */
    public function create(BookingData $data): Booking
    {
        return Booking::create([
            'user_id' => $data->user_id,
            'package_variant_id' => $data->package_variant_id,
            'background_id' => $data->background_id,
            'booking_code' => $data->booking_code,
            'booking_date' => $data->booking_date,
            'start_time' => $data->start_time,
            'end_time' => $data->end_time,
            'total_price' => $data->total_price,
            'payment_scheme' => $data->payment_scheme,
            'keterangan' => $data->keterangan,
            'status' => $data->status,
        ]);
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
}
