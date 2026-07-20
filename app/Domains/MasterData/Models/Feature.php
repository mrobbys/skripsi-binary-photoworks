<?php

namespace App\Domains\MasterData\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable('description', 'featureable_type', 'featureable_id')]
class Feature extends Model
{
    use LogsActivity;

    /**
     * Mendapatkan parent model (Package atau PackageVariant)
     */
    public function featureable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('master-data')
            ->setDescriptionForEvent(fn(string $event) => $event);
    }
}
