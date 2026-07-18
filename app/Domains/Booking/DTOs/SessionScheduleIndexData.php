<?php

namespace App\Domains\Booking\DTOs;

use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Models\Booking;
use App\Support\Formatter;
use Carbon\Carbon;
use Spatie\LaravelData\Data;

class SessionScheduleIndexData extends Data
{
  public function __construct(
    public readonly int $id,
    public readonly string $booking_code,
    public readonly string $user_name,
    public readonly string $user_email,
    public readonly string $user_phone,
    public readonly string $package_name,
    public readonly string $variant_name,
    public readonly string $background_name,
    public readonly string $booking_status,
    public readonly string $session_status,
    public readonly string $formatted_date,
    public readonly string $formatted_time,
    public readonly ?string $gdrive_link,
    public readonly ?string $notes,
  ) {}

  public static function fromModel(Booking $booking): self
  {
    $now = Carbon::now();
    $bookingDate = Carbon::parse($booking->booking_date)->startOfDay();
    $today = $now->copy()->startOfDay();

    $startTime = Carbon::parse($booking->start_time);
    $endTime = Carbon::parse($booking->end_time);

    $todayStart = $today->copy()->setTimeFrom($startTime);
    $todayEnd = $today->copy()->setTimeFrom($endTime);

    $isPassed = $bookingDate->lt($today) || ($bookingDate->eq($today) && $now->gt($todayEnd));

    $sessionStatus = match (true) {
      $booking->status === BookingStatus::DONE => 'SELESAI',
      $isPassed && $booking->status === BookingStatus::DP_PAID => 'MENUNGGU PELUNASAN',
      $isPassed && $booking->status === BookingStatus::SUCCESS => 'MENUNGGU UPLOAD',
      $bookingDate->gt($today) => 'MENDATANG',
      $bookingDate->eq($today) && $now->lt($todayStart) => 'MENUNGGU',
      $bookingDate->eq($today) && $now->between($todayStart, $todayEnd) => 'SEDANG BERLANGSUNG',
      default => 'SELESAI',
    };

    return new self(
      id: $booking->id,
      booking_code: $booking->booking_code,
      user_name: $booking->user->name,
      user_email: $booking->user->email,
      user_phone: $booking->user->phone,
      package_name: $booking->packageVariant->package->name . ' — ' . $booking->packageVariant->name,
      variant_name: $booking->packageVariant->name,
      background_name: $booking->background?->name ?? '-',
      booking_status: $booking->status->value,
      session_status: $sessionStatus,
      formatted_date: Formatter::dateId($booking->booking_date, 'l, d F Y'),
      formatted_time: Formatter::timeRange($booking->start_time, $booking->end_time),
      gdrive_link: $booking->gdrive_link,
      notes: $booking->notes,
    );
  }
}
