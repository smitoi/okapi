<?php

namespace App\Models\Okapi;

use Illuminate\Database\Eloquent\Model;

class Rule extends Model
{
    protected $table = 'okapi_rules';

    protected $casts = [
        'properties' => 'object',
    ];

    protected $fillable = [
        'name',
        'properties',
        'okapi_field_id',
    ];
}
