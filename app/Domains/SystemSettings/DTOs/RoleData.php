<?php

namespace App\Domains\SystemSettings\DTOs;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\LaravelData\Data;

class RoleData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly array $permissions = [],
    ) {}

    public static function fromRequest(FormRequest $request): self
    {
        return new self(
            name: strtolower(trim((string) $request->validated('name'))),
            permissions: $request->validated('permissions') ?? [],
        );
    }
}
