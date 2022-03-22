<?php

namespace App\Models\Okapi;

use App\Models\Traits\HandleSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Relationship extends Model
{
    use HandleSlug;

    protected $table = 'okapi_relationships';

    public const TYPE_BELONGS_TO = 'boolean';
    public const TYPE_HAS_MANY = 'number';
    public const TYPE_HAS_ONE = 'string';
    public const TYPE_BELONGS_TO_MANY = 'enum';

    public const TYPES = [
        self::TYPE_BELONGS_TO => 'Belongs to',
        self::TYPE_HAS_MANY => 'Has many',
        self::TYPE_HAS_ONE => 'Has one',
        self::TYPE_BELONGS_TO_MANY => 'Has many',
    ];

    protected $fillable = [
        'name',
        'type',
        'okapi_type_from_id',
        'okapi_type_to_id',
    ];
}
