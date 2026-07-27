<?php

namespace App\Domains\Review\DTOs;

use App\Domains\Review\Http\Requests\StoreReviewRequest;
use App\Domains\Review\Models\Review;

class ReviewItemData
{
    public function __construct(
        public readonly int $id,
        public readonly int $user_id,
        public readonly string $user_name,
        public readonly int $rating,
        public readonly string $comment,
        public readonly string $formatted_time,
        public readonly bool $is_mine,
        public readonly string $created_at,
    ) {}

    public static function fromModel(Review $review, ?int $currentUserId = null): self
    {
        return new self(
            id: $review->id,
            user_id: $review->user_id,
            user_name: strtoupper($review->user?->name ?? 'Pelanggan'),
            rating: (int) $review->rating,
            comment: $review->comment,
            formatted_time: $review->created_at->locale('id')->diffForHumans(),
            is_mine: $currentUserId !== null && $review->user_id === $currentUserId,
            created_at: $review->created_at->toISOString(),
        );
    }

    /**
     * Sanitize input dari StoreReviewRequest sebelum masuk ke database.
     */
    public static function fromRequest(StoreReviewRequest $request): array
    {
        return [
            'rating' => (int) $request->validated('rating'),
            'comment' => trim($request->validated('comment')),
        ];
    }
}
