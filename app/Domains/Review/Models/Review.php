<?php

namespace App\Domains\Review\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Relasi ke User (klien yang mereview) atau Booking jika diperlukan
    // public function user() { return $this->belongsTo(User::class); }
}
