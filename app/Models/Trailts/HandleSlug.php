<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HandleSlug
{
    protected string $sluggedColumn = 'name';
    protected string $slugColumn = 'slug';

    public static function bootHandleSlug(): void
    {
        static::saving(function (Model $model) {
            $model->{$model->slugColumn} = Str::slug($model->{$model->sluggedColumn});
        });
    }
}
