<?php

namespace App\Domains\MasterData\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable('description', 'featureable_type', 'featureable_id')]
class Feature extends Model
{
    /**
     * Mendapatkan parent model (Package atau PackageVariant)
     */
    public function featureable(): MorphTo
    {
        return $this->morphTo();
    }
}
