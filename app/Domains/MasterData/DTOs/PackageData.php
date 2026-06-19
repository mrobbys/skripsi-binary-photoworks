<?php

namespace App\Domains\MasterData\DTOs;

use Spatie\LaravelData\Data;

class PackageData extends Data
{
  public function __construct(
    public readonly int $category_id,
    public readonly string $name,
    public readonly bool $is_active = true,
    public readonly array $features = [],
  ) {}
}
