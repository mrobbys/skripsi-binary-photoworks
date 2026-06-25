<?php

namespace App\Support\MediaLibrary;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class CustomPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        return $this->getBasePath($media) . '/';
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->getBasePath($media) . '/conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getBasePath($media) . '/responsive-images/';
    }

    protected function getBasePath(Media $media): string
    {
        $folder = 'others';
        $modelClass = \Illuminate\Database\Eloquent\Relations\Relation::getMorphedModel($media->model_type) ?? $media->model_type;
        
        if (class_exists($modelClass)) {
            $model = new $modelClass;
            $folder = $model->getTable();
        }
        
        return $folder . '/' . $media->id;
    }
}
