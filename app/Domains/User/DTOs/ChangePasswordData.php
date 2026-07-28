<?php

namespace App\Domains\User\DTOs;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\LaravelData\Data;

class ChangePasswordData extends Data
{
    public function __construct(
        public readonly string $old_password,
        public readonly string $password,
    ) {}

    public static function fromRequest(FormRequest $request): self
    {
        return new self(
            old_password: $request->validated('old_password'),
            password: $request->validated('password'),
        );
    }
}
