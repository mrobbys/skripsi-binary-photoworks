<?php

namespace App\Domains\MasterData\DTOs;

use Spatie\LaravelData\Data;

class PackageVariantData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly int $price,
        public readonly int $duration,
        public readonly bool $is_whatsapp_only = false,
        public readonly bool $is_active = true,
        public readonly array $features = [],
    ) {}
}