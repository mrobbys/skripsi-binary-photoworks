<?php

namespace App\Domains\Auth\DTOs;

use Spatie\LaravelData\Data;

class ForgotPasswordData extends Data
{
  public function __construct(public readonly string $email) {}
}
