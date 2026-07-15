<?php

namespace App\Domains\Booking\DTOs;

use App\Domains\Booking\Http\Requests\StoreManualBookingRequest;
use Spatie\LaravelData\Data;

class ManualBookingData extends Data
{
  public function __construct(
    public readonly int $user_id,
    public readonly int $package_variant_id,
    public readonly int $background_id,
    public readonly string $booking_date,
    public readonly string $start_time,
    public readonly string $status,
    public readonly bool $send_wa_notification,
    public readonly array $addons = [],
  ) {}

  public static function fromRequest(StoreManualBookingRequest $request): self
  {
    return new self(
      user_id: (int) $request->validated('user_id'),
      package_variant_id: (int) $request->validated('package_variant_id'),
      background_id: (int) ($request->validated('background_id')),
      booking_date: $request->validated('booking_date'),
      start_time: $request->validated('start_time'),
      status: $request->validated('status'),
      send_wa_notification: (bool) $request->validated('send_wa_notification'),
      addons: $request->validated('addons') ?? [],
    );
  }
}
