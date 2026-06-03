<?php

namespace App\Domains\Auth\DTOs;

use Spatie\LaravelData\Data;

class RegisterData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
        public readonly string $phone,
    ) {}
}
