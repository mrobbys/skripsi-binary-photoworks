<?php

namespace App\Domains\MasterData\DTOs;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\LaravelData\Data;

class CategoryData extends Data
{
  public function __construct(
    public readonly string $category_code,
    public readonly string $name,
    public readonly bool $is_active = true
  ) {}

  public static function fromRequest(FormRequest $request): self
  {
    return new self(
      category_code: strtoupper(trim($request->validated('category_code'))),
      name: trim($request->validated('name')),
      is_active: (bool) $request->validated('is_active')
    );
  }
}
