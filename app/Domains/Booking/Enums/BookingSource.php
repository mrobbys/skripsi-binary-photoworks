<?php

namespace App\Domains\Booking\Enums;

enum BookingSource: string
{
  case FRONTDOOR = 'frontdoor';
  case MANUAL = 'manual';
}
