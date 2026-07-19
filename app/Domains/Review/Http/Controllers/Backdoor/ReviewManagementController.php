<?php

namespace App\Domains\Review\Http\Controllers\Backdoor;

use App\Domains\Review\DTOs\ClientReviewRowData;
use App\Domains\Review\DTOs\ClientReviewStatsData;
use App\Domains\Review\Models\Review;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewManagementController extends Controller
{
    /**
     * Halaman daftar ulasan.
     */
    public function index(): View
    {
        return view('backdoor.client-reviews.index');
    }

    /**
     * JSON endpoint untuk useDatatable.
     */
    public function data(Request $request): JsonResponse
    {
        $search = $request->input('search', '');
        $limit  = max(1, min($request->integer('limit', 10), 100));

        $query = Review::with('user')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->whereHas('user', fn($u) => $u->where('name', 'ilike', "%{$search}%"));
                    
                    // Jika pencarian adalah nilai rating, pastikan nilai tersebut adalah angka
                    if (is_numeric($search)) {
                        $inner->orWhere('rating', $search);
                    }
                });
            })
            ->latest('created_at');

        $paginated = $query->paginate($limit);

        return response()->json([
            'data' => ClientReviewRowData::collect($paginated->items()),
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'total' => $paginated->total(),
        ]);
    }

    /**
     * JSON endpoint untuk stats card.
     */
    public function stats(): JsonResponse
    {
        $stats = new ClientReviewStatsData(
            average_rating: round(Review::avg('rating') ?? 0, 1),
            total_reviews: Review::count(),
            five_star_reviews: Review::where('rating', 5)->count(),
            disappointing_reviews: Review::where('rating', '<=', 2)->count(),
        );

        return response()->json($stats);
    }

    /**
     * Hapus satu review.
     */
    public function destroy(Review $review): JsonResponse
    {
        try {
            $review->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Ulasan berhasil dihapus.'
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan server.'
            ], 500);
        }
    }
}
