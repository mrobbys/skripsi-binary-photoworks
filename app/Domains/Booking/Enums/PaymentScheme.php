<?php

namespace App\Domains\Booking\Enums;

enum PaymentScheme: string
{
  case LUNAS = 'lunas';
  case DP = 'dp';

  public const DP_RATE = 0.6;
}

