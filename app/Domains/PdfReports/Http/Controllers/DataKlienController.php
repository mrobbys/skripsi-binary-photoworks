<?php

namespace App\Domains\PdfReports\Http\Controllers;

use App\Domains\PdfReports\Http\Requests\DateRangeReportRequest;
use App\Domains\User\Enums\RoleType;
use App\Domains\User\Models\User;
use App\Http\Controllers\Controller;
use App\Domains\PdfReports\Traits\HasPdfMetadata;
use App\Support\Formatter;
use Carbon\Carbon;
use Illuminate\Support\Fluent;

use function Spatie\LaravelPdf\Support\pdf;

class DataKlienController extends Controller
{
  use HasPdfMetadata;

  public function __invoke(DateRangeReportRequest $request)
  {
    $startDate = Carbon::parse($request->validated('start_date'))->startOfDay();
    $endDate = Carbon::parse($request->validated('end_date'))->endOfDay();

    $users = User::select('id', 'name', 'phone', 'email', 'created_at')
      ->role(RoleType::USER->value)
      ->withCount('bookings')
      ->whereBetween('created_at', [$startDate, $endDate])
      ->orderBy('created_at', 'asc')
      ->get();

    $rows = $users->map(fn($user, $index) => new Fluent([
      'no' => $index + 1,
      'nama_klien' => $user->name,
      'whatsapp' => $user->phone ?? '-',
      'email' => $user->email,
      'total_booking' => $user->bookings_count,
      'tanggal_bergabung' => Formatter::dateId($user->created_at),
    ]));

    $totalKlien = $users->count();
    $filterText = Formatter::dateId($startDate) . ' s/d ' . Formatter::dateId($endDate);

    return pdf()
      ->view('pdfs.data-klien', $this->pdfData(compact('rows', 'totalKlien', 'filterText')))
      ->format('a4')
      ->portrait()
      ->margins(10, 10, 10, 10)
      ->name("laporan-data-klien-{$startDate->format('d-m-Y')}-sd-{$endDate->format('d-m-Y')}.pdf");
  }
}
