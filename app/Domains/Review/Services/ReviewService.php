<?php

namespace App\Domains\Review\Services;

use App\Domains\Review\Models\Review;
use Illuminate\Support\Fluent;

class ReviewService
{
    /**
     * Kumpulan data statistik untuk ulasan
     */
    public function getStats(): Fluent
    {
        $aggregate = Review::selectRaw('AVG(rating) as average, COUNT(*) as total')->first();

        $averageRating = round((float) ($aggregate->average ?? 0), 1);
        $totalReviews = (int) ($aggregate->total ?? 0);

        $raw = Review::query()
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        $breakdown = [];
        $percentages = [];
        $fiveStar = 0;
        $disappointing = 0;

        for ($star = 5; $star >= 1; $star--) {
            $count = (int) ($raw[$star] ?? 0);
            $breakdown[$star] = $count;
            $percentages[$star] = $totalReviews > 0
                ? (int) round(($count / $totalReviews) * 100)
                : 0;

            if ($star === 5) $fiveStar = $count;
            if ($star <= 2) $disappointing += $count;
        }

        return new Fluent([
            'average_rating' => $averageRating,
            'total_reviews' => $totalReviews,
            'five_star_reviews' => $fiveStar,
            'disappointing_reviews' => $disappointing,
            'breakdown' => $breakdown,
            'breakdown_percentage' => $percentages,
        ]);
    }

    /**
     * Mendapatkan data reiew berdasarkan user_id
     * @param int $userId
     */
    public function getUserReview(int $userId): ?Review
    {
        return Review::with('user:id,name')->where('user_id', $userId)->first();
    }

    /**
     * Memeriksa apakah user telah memberikan ulasan
     * @param int $userId
     */
    public function userHasReview(int $userId): bool
    {
        return Review::where('user_id', $userId)->exists();
    }
}
