<?php

namespace App\Domains\Review\Http\Controllers\Backdoor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReviewManagementController extends Controller
{
    public function index()
    {
        // Menampilkan daftar semua ulasan ke halaman admin
        return view('backdoor.reviews.index');
    }

    // public function destroy($id) { ... }
}
