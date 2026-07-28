<?php

namespace App\Domains\Review\Http\Controllers\Frontdoor;

use App\Domains\Review\DTOs\ReviewItemData;
use App\Domains\Review\Http\Requests\StoreReviewRequest;
use App\Domains\Review\Models\Review;
use App\Domains\Review\Services\ReviewService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Domains\Review\Enums\ReviewSort;

class ReviewController extends Controller
{
    public function __construct(
        private readonly ReviewService $reviewService,
    ) {}

    public function index(): View
    {
        return view('frontdoor.review.index');
    }

    /**
     * Tampilkan data review untuk di halaman index
     * @param Request $request
     */
    public function data(Request $request): JsonResponse
    {
        $sort = $request->query('sort', ReviewSort::NEWEST->value);
        $limit = max(1, min((int) $request->query('limit', 10), 100));
        $userId = Auth::id();

        $query = Review::select(['id', 'user_id', 'rating', 'comment', 'created_at'])
            ->with('user:id,name');

        match ($sort) {
            ReviewSort::HIGHEST->value => $query->orderBy('rating', 'desc')->latest('created_at'),
            ReviewSort::LOWEST->value => $query->orderBy('rating', 'asc')->latest('created_at'),
            default => $query->latest('created_at'),
        };

        $paginated = $query->paginate($limit);

        $items = collect($paginated->items())
            ->map(fn(Review $review) => ReviewItemData::fromModel($review, $userId));

        $userReview = null;
        if ($userId) {
            $myReview = $this->reviewService->getUserReview($userId);
            if ($myReview) {
                $userReview = ReviewItemData::fromModel($myReview, $userId);
            }
        }

        $stats = $this->reviewService->getStats();

        return response()->json([
            'data' => $items,
            'user_review' => $userReview,
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'total' => $paginated->total(),
            'stats' => $stats,
        ]);
    }

    /**
     * Simpan data review
     * @param StoreReviewRequest $request
     */
    public function store(StoreReviewRequest $request): JsonResponse
    {
        $userId = Auth::id();

        // Cek apakah user sudah memberikan ulasan
        if ($this->reviewService->userHasReview($userId)) {
            return $this->errorResponse('Anda sudah memberikan ulasan. Hapus ulasan lama Anda terlebih dahulu untuk membuat ulasan baru.', 422);
        }

        try {
            $review = Review::create(array_merge(
                ReviewItemData::fromRequest($request),
                ['user_id' => $userId],
            ));

            $review->load('user:id,name');

            return $this->successResponse('Ulasan Anda berhasil dikirim. Terima kasih!', ReviewItemData::fromModel($review, $userId), 201);
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan server');
        }
    }

    /**
     * Hapus data review
     * @param Review $review
     */
    public function destroy(Review $review): JsonResponse
    {
        // Cek apakah data review milik user yang sedang login
        if ($review->user_id !== Auth::id()) {
            return $this->errorResponse('Anda tidak dapat menghapus ulasan milik orang lain.', 403);
        }

        try {
            $review->delete();

            return $this->successResponse('Ulasan berhasil dihapus.');
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan server');
        }
    }
}
