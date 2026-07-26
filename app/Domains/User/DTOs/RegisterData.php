<?php

namespace App\Domains\User\DTOs;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\LaravelData\Data;

class RegisterData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
        public readonly string $phone,
    ) {}

    public static function fromRequest(FormRequest $request): self
    {
        return new self(
            name: trim($request->validated('name')),
            email: trim($request->validated('email')),
            password: $request->validated('password'),
            phone: trim($request->validated('phone')),
        );
    }
}
