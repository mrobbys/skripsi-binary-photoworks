<?php

namespace App\Domains\Booking\Enums;

enum BookingStatus: string
{
    case PENDING = 'Menunggu';
    case DP_PAID = 'DP Terbayar';
    case SUCCESS = 'Lunas';
    case CANCELLED = 'Batal';
    case DONE = 'Selesai';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Pembayaran',
            self::DP_PAID => 'DP Terbayar (60%)',
            self::SUCCESS => 'Lunas (100%)',
            self::CANCELLED => 'Dibatalkan',
            self::DONE => 'Selesai',
        };
    }
}
