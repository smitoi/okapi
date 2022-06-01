<?php

namespace App\Models\Okapi;

use App\Models\Traits\HandleSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property string $name
 * @property string $reverse_name
 * @property string $slug
 * @property string $reverse_slug
 * @property bool $reverse_visible
 * @property string $type
 * @property int $okapi_type_from_id
 * @property int $okapi_type_to_id
 * @property int $okapi_field_display_id
 * @property int $reverse_okapi_field_display_id
 *
 * @property Field $displayField
 * @property Field $reverseDisplayField
 *
 * @property Type $toType
 * @property Type $fromType
 *
 * Class Relationship
 * @package App\Models\Okapi
 */
class Relationship extends Model
{
    use HandleSlug;

    protected array $slugColumns = [
        'name' => 'slug',
        'reverse_name' => 'reverse_slug',
    ];

    protected $table = 'okapi_relationships';

    protected $fillable = [
        'name',
        'slug',
        'reverse_name',
        'reverse_slug',
        'reverse_visible',
        'type',
        'okapi_type_from_id',
        'okapi_type_to_id',
        'okapi_field_display_id',
        'reverse_okapi_field_display_id',
    ];

    protected $casts = [
        'reverse_visible' => 'boolean',
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

    public function reverseDisplayField(): HasOne
    {
        return $this->hasOne(Field::class, 'id', 'reverse_okapi_field_display_id');
    }
}
