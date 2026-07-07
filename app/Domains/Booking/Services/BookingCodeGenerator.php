<?php

namespace App\Domains\Booking\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;

class BookingCodeGenerator
{
    /**
     * Format: BPW-{CAT_CODE}{VAR_ID_02d}-{YYMMDD}-{RAND3}
     * Contoh: BPW-WSD01-260530-7AX
     */
    public function generate(string $categoryCode, int $variantId, string $bookingDate): string
    {
        $packageSegment = strtoupper($categoryCode).str_pad($variantId, 2, '0', STR_PAD_LEFT);
        $dateSegment = Carbon::parse($bookingDate)->format('ymd');
        $randomSegment = Str::upper(Str::random(3));

        return "BPW-{$packageSegment}-{$dateSegment}-{$randomSegment}";
    }
}
