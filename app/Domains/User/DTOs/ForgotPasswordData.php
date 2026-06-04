<?php

namespace App\Domains\User\DTOs;

use Spatie\LaravelData\Data;

class ForgotPasswordData extends Data
{
  public function __construct(public readonly string $email) {}
}
