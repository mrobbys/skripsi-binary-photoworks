<?php

namespace App\Domains\User\DTOs;

use App\Domains\User\Models\User;
use App\Support\Formatter;
use Spatie\LaravelData\Data;

class ClientDataIndexData extends Data
{
  public function __construct(
    public readonly string $uuid,
    public readonly string $name,
    public readonly string $email,
    public readonly ?string $phone,
    public readonly string $joined_at,
    public readonly int $bookings_count
  ) {}

  public static function fromModel(User $user): self
  {
    return new self(
      uuid: $user->uuid,
      name: $user->name,
      email: $user->email,
      phone: $user->phone,
      joined_at: Formatter::dateId($user->created_at),
      bookings_count: $user->bookings_count ?? 0
    );
  }
}
