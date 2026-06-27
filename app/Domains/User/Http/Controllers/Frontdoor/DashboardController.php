<?php

namespace App\Domains\User\Http\Controllers\Frontdoor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        return view('frontdoor.dashboard.jadwal');
    }

    public function profil()
    {
        return view('frontdoor.dashboard.profil');
    }
}
