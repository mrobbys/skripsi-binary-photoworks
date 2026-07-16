<?php

namespace App\Domains\Payment\Enums;

enum PaymentStatus: string
{
    case PENDING = 'Pending';
    case SETTLEMENT = 'Settlement';
    case CANCELLED = 'Batal';
    case REFUNDED = 'Refunded';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Pembayaran',
            self::SETTLEMENT => 'Settlement',
            self::CANCELLED => 'Dibatalkan',
            self::REFUNDED => 'Dikembalikan (Refund)',
        };
    }
}
