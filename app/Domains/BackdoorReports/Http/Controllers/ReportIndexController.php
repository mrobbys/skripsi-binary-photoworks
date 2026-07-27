<?php

namespace App\Domains\BackdoorReports\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Routing\Attributes\Controllers\Middleware;

#[Middleware('permission:report-view', only: ['index'])]
class ReportIndexController extends Controller
{
  public function index()
  {
    return view('backdoor.reports.index');
  }
}