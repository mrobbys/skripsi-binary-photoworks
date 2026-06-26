<?php

namespace App\Domains\MasterData\DTOs;

use Spatie\LaravelData\Data;

class AddonData extends Data
{
  public function __construct(
    public readonly string $name,
    public readonly int $price,
    public readonly string $description,
    public readonly bool $has_quantity = false,
    public readonly bool $is_active = true,
  ) {}
}
