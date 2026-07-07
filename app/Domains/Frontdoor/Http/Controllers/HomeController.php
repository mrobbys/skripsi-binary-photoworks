<?php

namespace App\Domains\Frontdoor\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        return view('frontdoor.home.index');
    }
}
