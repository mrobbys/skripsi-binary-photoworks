<?php

namespace App\Domains\Review\DTOs;

use App\Domains\Review\Models\Review;
use Spatie\LaravelData\Data;
use App\Support\Formatter;

class ClientReviewRowData extends Data
{
  public function __construct(
    public readonly int $id,
    public readonly string $client_name,
    public readonly int $rating,
    public readonly string $comment,
    public readonly string $created_at,
  ) {}

  public static function fromModel(Review $review): self
  {
    return new self(
      id: $review->id,
      client_name: $review->user->name,
      rating: $review->rating,
      comment: $review->comment,
      created_at: Formatter::dateId($review->created_at, 'l, d F Y'),
    );
  }
}
