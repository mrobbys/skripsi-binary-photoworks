<?php

namespace App\Domains\Booking\Services;

use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Models\Booking;
use App\Domains\Payment\Enums\PaymentStatus;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SlotAvailabilityService
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
