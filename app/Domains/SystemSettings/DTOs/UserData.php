<?php

namespace App\Domains\SystemSettings\DTOs;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\LaravelData\Data;

class UserData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $phone,
        public readonly string $role,
    ) {}

    public static function fromRequest(FormRequest $request): self
    {
        return new self(
            name: trim($request->validated('name')),
            email: strtolower(trim($request->validated('email'))),
            phone: trim($request->validated('phone')),
            role: $request->validated('role'),
        );
    }
}
