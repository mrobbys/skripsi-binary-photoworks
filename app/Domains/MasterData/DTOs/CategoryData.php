<?php

namespace App\Domains\MasterData\DTOs;

use Spatie\LaravelData\Data;

class CategoryData extends Data
{
  public function __construct(
    public readonly string $category_code,
    public readonly string $name,
    public readonly bool $is_active = true
  ) {}
}
