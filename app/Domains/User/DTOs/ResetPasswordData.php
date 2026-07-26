<?php

namespace App\Domains\User\DTOs;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\LaravelData\Data;

class ResetPasswordData extends Data
{
    public function __construct(
        public readonly string $token,
        public readonly string $email,
        public readonly string $password,
    ) {}

    public static function fromRequest(FormRequest $request): self
    {
        return new self(
            token: $request->validated('token'),
            email: trim($request->validated('email')),
            password: $request->validated('password'),
        );
    }
}
