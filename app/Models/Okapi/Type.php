<?php

namespace App\Models\Okapi;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Type extends Model
{
    protected $table = 'okapi_types';

    protected $fillable = [
        'name',
        'slug',
        'is_collection',
        'type'
    ];

    protected $casts = [
        'is_collection' => 'bool',
    ];

    public function fields(): HasMany
    {
        return $this->hasMany(Field::class, 'okapi_type_id', 'id');
    }
}
