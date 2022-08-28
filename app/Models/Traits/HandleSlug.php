<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HandleSlug
{
    public static function bootHandleSlug(): void
    {
        static::saving(function (Model $model) {
            assert(empty($model->slugColumns) === false);
            foreach ($model->slugColumns as $sluggedColumn => $slugColumn) {
                $model->{$slugColumn} = Str::slug($model->{$sluggedColumn});
            }
        });
    }
}
