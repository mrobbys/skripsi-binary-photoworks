<?php

namespace App\Domains\SystemSettings\DTOs;

use App\Domains\User\Models\User;
use Spatie\LaravelData\Data;

class UserRowData extends Data
{
  public function __construct(
    public readonly int $id,
    public readonly string $name,
    public readonly string $email,
    public readonly string $phone,
    public readonly string $role,
  ) {}

  public static function fromUser(User $user): self
  {
    return new self(
      id: $user->id,
      name: $user->name,
      email: $user->email,
      phone: $user->phone ?? '-',
      role: $user->getRoleNames()->first() ?? '-',
    );
  }
}
