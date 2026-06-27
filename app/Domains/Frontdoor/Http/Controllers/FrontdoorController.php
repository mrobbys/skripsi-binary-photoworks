<?php

namespace App\Domains\Frontdoor\Http\Controllers;

use App\Http\Controllers\Controller;

class FrontdoorController extends Controller
{
  public function index()
  {
    return view('frontdoor.home.index');
  }
}