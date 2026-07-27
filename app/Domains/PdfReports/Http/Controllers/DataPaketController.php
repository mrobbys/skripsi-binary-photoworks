<?php

namespace App\Domains\PdfReports\Http\Controllers;

use App\Domains\MasterData\Models\PackageVariant;
use App\Http\Controllers\Controller;
use App\Domains\PdfReports\Traits\HasPdfMetadata;
use App\Support\Formatter;
use Illuminate\Support\Fluent;

use function Spatie\LaravelPdf\Support\pdf;

class DataPaketController extends Controller
{
    use HasPdfMetadata;

  public function __invoke()
  {
    $variants = PackageVariant::with([
      'package:id,category_id,name,is_active',
      'package.category:id,name,is_active',
    ])
      ->orderBy('package_id')
      ->get();

    $rows = $variants->map(function ($variant, $index) {
      $isActive = $variant->is_active
        && ($variant->package?->is_active ?? false)
        && ($variant->package?->category?->is_active ?? false);

      return new Fluent([
        'no' => $index + 1,
        'kategori' => $variant->package?->category?->name ?? '-',
        'nama_paket' => $variant->package?->name ?? '-',
        'varian' => $variant->name,
        'harga' => Formatter::rupiah($variant->price),
        'durasi' => $variant->duration,
        'booking_via' => $variant->is_whatsapp_only ? 'WhatsApp' : 'Sistem',
        'status' => $isActive ? 'Aktif' : 'Tidak Aktif',
      ]);
    });

    return pdf()
      ->view('pdfs.data-paket', $this->pdfData(compact('rows')))
      ->format('a4')
      ->landscape()
      ->margins(10, 10, 10, 10)
      ->name('laporan-data-paket-katalog-master.pdf');
  }
}
