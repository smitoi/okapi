<?php

namespace App\Models\Okapi;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Instance extends Model
{
    protected $table = 'okapi_instances';

    protected $fillable = [
        'okapi_type_id',
    ];

    public function values(): HasMany
    {
        return $this->hasMany(Field::class, 'okapi_instance_id', 'id');
    }
}
