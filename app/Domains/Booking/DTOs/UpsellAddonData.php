<?php

namespace App\Domains\Booking\DTOs;

use App\Domains\Booking\Http\Requests\UpsellAddonRequest;
use Spatie\LaravelData\Data;

class UpsellAddonData extends Data
{
  public function __construct(
    public readonly int $addon_id,
    public readonly int $quantity,
  ) {}

  public static function fromRequest(UpsellAddonRequest $request): self
  {
    return new self(
      addon_id: (int) $request->validated('addon_id'),
      quantity: (int) $request->validated('quantity'),
    );
  }
}
