<?php

namespace App\Domains\Frontdoor\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AboutController extends Controller
{
    public function index()
    {
        return view('frontdoor.about.index');
    }
}
