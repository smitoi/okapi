<?php

namespace App\Models\Okapi;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property bool $has_reverse
 * @property string $type
 * @property int $api_visibility
 * @property int $okapi_type_from_id
 * @property int $okapi_type_to_id
 * @property int $okapi_field_display_id
 *
 * Class Relationship
 * @package App\Models\Okapi
 */
class Relationship extends Model
{
    protected $table = 'okapi_relationships';

    protected $fillable = [
        'has_reverse',
        'type',
        'api_visibility',
        'okapi_type_from_id',
        'okapi_type_to_id',
        'okapi_field_display_id',
    ];

    protected $casts = [
        'has_reverse' => 'boolean',
    ];


    public function toType(): HasOne
    {
        return $this->hasOne(Type::class, 'id', 'okapi_type_to_id');
    }

    public function fromType(): HasOne
    {
        return $this->hasOne(Type::class, 'id', 'okapi_type_from_id');
    }

    public function displayField(): HasOne
    {
        return $this->hasOne(Field::class, 'id', 'okapi_field_display_id');
    }
}
