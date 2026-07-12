<?php

namespace App\Domains\MasterData\DTOs;

use App\Domains\MasterData\Http\Requests\StoreAddonRequest;
use App\Domains\MasterData\Http\Requests\UpdateAddonRequest;
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

  public static function fromRequest(StoreAddonRequest|UpdateAddonRequest $request): self
  {
    return new self(
      name: trim($request->validated('name')),
      price: (int) $request->validated('price'),
      description: trim($request->validated('description')),
      has_quantity: (bool) $request->validated('has_quantity'),
      is_active: (bool) $request->validated('is_active'),
    );
  }
}
