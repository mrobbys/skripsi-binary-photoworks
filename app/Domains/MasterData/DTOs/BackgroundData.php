<?php

namespace App\Domains\MasterData\DTOs;

use Spatie\LaravelData\Data;

class BackgroundData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description,
        public readonly bool $is_active = true,
    ) {}
}
