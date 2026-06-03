<?php

namespace App\Domains\MasterData\Enums;

enum DayOfWeek: int
{
  case MONDAY = 1;
  case TUESDAY = 2;
  case WEDNESDAY = 3;
  case THURSDAY = 4;
  case FRIDAY = 5;
  case SATURDAY = 6;
  case SUNDAY = 7;

  public function label(): string
  {
    return match ($this) {
      self::MONDAY => 'Senin',
      self::TUESDAY => 'Selasa',
      self::WEDNESDAY => 'Rabu',
      self::THURSDAY => 'Kamis',
      self::FRIDAY => 'Jumat',
      self::SATURDAY => 'Sabtu',
      self::SUNDAY => 'Minggu',
    };
  }
}
