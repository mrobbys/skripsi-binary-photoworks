<?php

namespace App\Domains\Review\Http\Controllers\Frontdoor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index()
    {
        return view('frontdoor.reviews.index');
    }

    // Method untuk form memberikan ulasan oleh klien setelah selesai sesi
    // public function store(Request $request) { ... }
}
