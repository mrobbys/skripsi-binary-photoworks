<?php

namespace App\Domains\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domains\Booking\Models\Booking;

use App\Domains\Payment\Enums\PaymentStatus;

#[Guarded(['id'])]
class Payment extends Model
{
    protected function casts(): array
    {
        return [
            'pay_date' => 'datetime',
            'status' => PaymentStatus::class,
        ];
    }
    
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
