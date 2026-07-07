<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Number;

class Formatter
{
  /**
   * Format mata uang rupiah
   * 
   * @param int|float|null $amount
   */
  public static function rupiah(int|float|null $amount): string
  {
    if (is_null($amount)) {
      return 'Rp 0';
    }

    $formatted = 'Rp ' . Number::format($amount, locale: 'id');

    return $amount < 0 ? "-{$formatted}" : $formatted;
  }

  /**
   * Format tanggal indonesia
   * Contoh : '01 Januari 2026'
   * 
   * @param mixed $date
   * @param string $format
   */
  public static function dateId(mixed $date, string $format = 'd F Y'): string
  {
    if (!$date) return '-';
    return Carbon::parse($date)->locale('id')->translatedFormat($format);
  }

  /**
   * Format time range 
   * Contoh : '08:00 - 10:00'
   * 
   * @param mixed $start
   * @param mixed $end
   */
  public static function timeRange(mixed $start, mixed $end): string
  {
    if (!$start || !$end) return '-';

    $startTime = Carbon::parse($start)->format('H:i');
    $endTime = Carbon::parse($end)->format('H:i');

    return "{$startTime} - {$endTime} WITA";
  }
}
