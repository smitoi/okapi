<?php

namespace App\Models\Okapi;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Rule extends Model
{
    protected $table = 'okapi_rules';

    protected $fillable = [
        'name',
        'value',
        'okapi_field_id',
    ];

    public function value(): Attribute
    {
        return new Attribute(
            get: fn($value, $attributes) => in_array($attributes['name'], [
            'accepted', 'declined', 'required', 'unique',
        ]) ? (boolean)$value : $value);
    }
}
