<?php

namespace App\Domains\BackdoorReports\Http\Controllers;

use App\Http\Controllers\Controller;

class ReportIndexController extends Controller
{
  public function index()
  {
    return view('backdoor.reports.index');
  }
}