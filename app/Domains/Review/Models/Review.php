<?php

namespace App\Domains\Review\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domains\User\Models\User;
use Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Guarded(['id'])]
#[UseFactory(ReviewFactory::class)]
class Review extends Model
{
    use HasFactory, LogsActivity;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Implement Activity Log Spatie
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logUnguarded()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('review')
            ->setDescriptionForEvent(fn(string $event) => $event);
    }
}
