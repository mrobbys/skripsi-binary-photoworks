<?php

namespace App\Domains\User\DTOs;

use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\User\Models\User;
use App\Support\Formatter;
use Spatie\LaravelData\Data;

class ClientDataShowData extends Data
{
  public function __construct(
    public readonly string $uuid,
    public readonly string $name,
    public readonly string $email,
    public readonly ?string $phone,
    public readonly string $joined_at,
    public readonly int $total_all,
    public readonly int $total_pending,
    public readonly int $total_dp,
    public readonly int $total_success,
    public readonly int $total_done,
    public readonly int $total_cancel,
  ) {}

  public static function fromModel(User $user): self
  {
    return new self(
      uuid: $user->uuid,
      name: $user->name,
      email: $user->email,
      phone: $user->phone,
      joined_at: Formatter::dateId($user->created_at, 'l, d F Y'),
      total_all: $user->total_all ?? 0,
      total_pending: $user->total_pending ?? 0,
      total_dp: $user->total_dp ?? 0,
      total_success: $user->total_success ?? 0,
      total_done: $user->total_done ?? 0,
      total_cancel: $user->total_cancel ?? 0
    );
  }
}
