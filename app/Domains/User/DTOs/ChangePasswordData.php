<?php

namespace App\Domains\User\DTOs;

use App\Domains\User\Http\Requests\ChangePasswordRequest;
use Spatie\LaravelData\Data;

class ChangePasswordData extends Data
{
    public function __construct(
        public readonly string $old_password,
        public readonly string $password,
    ) {}

    public static function fromRequest(ChangePasswordRequest $request): self
    {
        return new self(
            old_password: $request->validated('old_password'),
            password: $request->validated('password'),
        );
    }
}
