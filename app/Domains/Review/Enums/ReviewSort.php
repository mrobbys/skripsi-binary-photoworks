<?php

namespace App\Domains\Review\Enums;

enum ReviewSort: string
{
  case NEWEST = 'newest';
  case HIGHEST = 'highest';
  case LOWEST = 'lowest';

  public function label(): string
  {
    return match ($this) {
      self::NEWEST => 'Terbaru',
      self::HIGHEST => 'Rating Tertinggi',
      self::LOWEST => 'Rating Terendah',
    };
  }
}
