<?php

namespace App\Domains\Payment\Enums;

enum PaymentPurpose: string
{
    case DP = 'dp';
    case PELUNASAN = 'pelunasan';
    case LUNAS = 'lunas';
    case REFUND = 'refund';
}
