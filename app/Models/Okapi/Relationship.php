<?php

namespace App\Models\Okapi;

use App\Models\Traits\HandleSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Relationship extends Model
{
    use HandleSlug;

    protected array $slugColumns = [
        'name' => 'slug',
        'reverse_name' => 'reverse_slug',
    ];

    protected $table = 'okapi_relationships';

    public const VALUE_FOR_ID_COLUMN = -1;

    public const TYPE_HAS_ONE = 'has one';
    public const TYPE_BELONGS_TO_ONE = 'belongs to one';
    public const TYPE_HAS_MANY = 'has many';
    public const TYPE_BELONGS_TO_MANY = 'belongs to many';

    public const TYPES = [
        self::TYPE_HAS_MANY => 'One to many',
        self::TYPE_BELONGS_TO_ONE => 'Many to one',
        self::TYPE_HAS_ONE => 'One to one',
        self::TYPE_BELONGS_TO_MANY => 'Many to many',
    ];

    public const REVERSE_RELATIONSHIPS = [
        self::TYPE_HAS_MANY => self::TYPE_BELONGS_TO_ONE,
        self::TYPE_BELONGS_TO_ONE => self::TYPE_HAS_MANY,
        self::TYPE_HAS_ONE => self::TYPE_HAS_ONE,
        self::TYPE_BELONGS_TO_MANY => self::TYPE_BELONGS_TO_MANY,
    ];

    protected $fillable = [
        'name',
        'reverse_name',
        'type',
        'okapi_type_from_id',
        'okapi_type_to_id',
        'okapi_field_display_id',
        'reverse_okapi_field_display_id',
    ];

    public function instances(): HasMany
    {
        return $this->hasMany(Instance::class, 'okapi_type_id', 'okapi_type_to_id');
    }

    public function display_field(): HasOne
    {
        return $this->hasOne(Field::class, 'id', 'okapi_field_display_id');
    }

    public function reverse_instances(): HasMany
    {
        return $this->hasMany(Instance::class, 'okapi_type_id', 'okapi_type_from_id');
    }

    public function reverse_display_field(): HasOne
    {
        return $this->hasOne(Field::class, 'id', 'reverse_okapi_field_display_id');
    }
}
