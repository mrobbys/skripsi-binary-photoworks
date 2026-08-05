<?php

namespace App\Domains\Frontdoor\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\MasterData\Models\Package;
use App\Domains\MasterData\Models\Schedule;
use App\Domains\Review\Models\Review;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('frontdoor.home.index', [
            'packages' => $this->getPackages(),
            'reviews' => $this->getReviews(),
            'schedules' => $this->getSchedules(),
        ]);
    }

    /**
     * Ambil 3 paket
     */
    private function getPackages()
    {
        // Ambil paket berdasarkan slug
        $prioritySlugs = ['personal-studio', 'graduation-studio', 'family-studio'];

        $packages = Package::query()
            ->select(['id', 'name', 'slug'])
            ->whereIn('slug', $prioritySlugs)
            ->where('is_active', true)
            ->with('media')
            ->get();
            
        // Jika data paket berdasarkan slug tidak ditemukan, fallback ke 3 paket aktif terbaru
        if ($packages->count() < 3) {
            $packages = Package::query()
                ->select(['id', 'name', 'slug'])
                ->where('is_active', true)
                ->with('media')
                ->latest()
                ->take(3)
                ->get();
        }

        return $packages;
    }

    /**
     * Ambil 3 review
     */
    private function getReviews()
    {
        return Review::query()
            ->with('user:id,name')
            ->latest()
            ->take(3)
            ->get();
    }

    /**
     * Ambil data jadwal (seluruh 7 hari)
     */
    private function getSchedules()
    {
        return Schedule::query()
            ->orderBy('id')
            ->get();
    }
}
