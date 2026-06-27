<?php

namespace App\Domains\Booking\Http\Controllers\Frontdoor;

use App\Http\Controllers\Controller;

class BookingController extends Controller
{
    public function services()
    {
        return view('frontdoor.services.index');
    }

    /**
     * Menampilkan halaman pertama booking flow (Pemilihan Paket & Add-on).
     */
    public function index()
    {
        return view('frontdoor.booking.pages.index');
    }
}
