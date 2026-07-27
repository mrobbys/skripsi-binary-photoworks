<?php

namespace App\Domains\PdfReports\Traits;

use App\Support\Formatter;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

trait HasPdfMetadata
{
    /**
     * Merge common PDF variables (printedBy, printDate) with specific data.
     */
    protected function pdfData(array $data = []): array
    {
        return array_merge([
            'printedBy' => Auth::user()?->name ?? 'Administrator',
            'printDate' => Formatter::dateId(Carbon::now()),
        ], $data);
    }
}
