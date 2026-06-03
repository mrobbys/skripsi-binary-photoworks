<?php

namespace App\Domains\MasterData\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use App\Domains\MasterData\Enums\DayOfWeek;

#[Fillable('day', 'start_time', 'end_time', 'is_active')]
class Schedule extends Model
{

    protected function casts(): array
    {
        return [
            'day' => DayOfWeek::class, // nanti consume di DTO
            'is_active' => 'boolean',
        ];
    }
}
