<?php

namespace App\Domains\PdfReports\Http\Controllers;

use App\Domains\PdfReports\Http\Requests\DateRangeReportRequest;
use App\Domains\Review\Models\Review;
use App\Http\Controllers\Controller;
use App\Domains\PdfReports\Traits\HasPdfMetadata;
use App\Support\Formatter;
use Carbon\Carbon;
use Illuminate\Support\Fluent;

use function Spatie\LaravelPdf\Support\pdf;

class RekapitulasiUlasanPelangganController extends Controller
{
    use HasPdfMetadata;

  public function __invoke(DateRangeReportRequest $request)
  {
    $startDate = Carbon::parse($request->validated('start_date'))->startOfDay();
    $endDate = Carbon::parse($request->validated('end_date'))->endOfDay();

    $reviews = Review::with(['user:id,name'])
      ->whereBetween('created_at', [$startDate, $endDate])
      ->orderBy('created_at', 'asc')
      ->get();

    $rows = $reviews->map(fn ($review, $index) => new Fluent([
      'no' => $index + 1,
      'tanggal' => Formatter::dateId($review->created_at, 'd-m-Y'),
      'nama_klien' => $review->user?->name ?? '-',
      'rating' => "{$review->rating}/5",
      'komentar' => $review->comment,
    ]));

    $totalUlasan = $reviews->count();
    $rataRataRating = $reviews->isNotEmpty()
      ? number_format($reviews->avg('rating'), 1)
      : '0.0';
    $filterText = Formatter::dateId($startDate) . ' s/d ' . Formatter::dateId($endDate);

    return pdf()
      ->view('pdfs.rekapitulasi-ulasan-pelanggan', $this->pdfData(compact('rows', 'totalUlasan', 'rataRataRating', 'filterText')))
      ->format('a4')
      ->portrait()
      ->margins(10, 10, 10, 10)
      ->name("laporan-rekapitulasi-ulasan-pelanggan-{$startDate->format('d-m-Y')}-sd-{$endDate->format('d-m-Y')}.pdf");
  }
}
