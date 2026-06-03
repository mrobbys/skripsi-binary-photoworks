<?php

namespace App\Domains\MasterData\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable('name', 'price', 'description', 'has_quantity', 'is_active')]
class Addon extends Model
{

    protected function casts(): array
    {
        return [
            'has_quantity' => 'boolean',
            'is_active'    => 'boolean',
        ];
    }
}
