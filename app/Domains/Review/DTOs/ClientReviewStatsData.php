<?php

namespace App\Domains\Review\DTOs;

use Spatie\LaravelData\Data;

class ClientReviewStatsData extends Data
{
  public function __construct(
    public readonly float $average_rating,
    public readonly int $total_reviews,
    public readonly int $five_star_reviews,
    public readonly int $disappointing_reviews,
  ) {}
}
