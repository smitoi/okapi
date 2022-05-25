<?php

namespace App\Models\Okapi;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Instance
 * @package App\Models\Okapi
 */
class Instance extends Model
{
    protected $table = 'okapi_instances';

    protected $fillable = [
        'okapi_type_id',
        'user_id',
    ];

    public function fields(): BelongsToMany
    {
        return $this->belongsToMany(
            Field::class,
            'okapi_instance_field',
            'okapi_instance_id',
            'okapi_field_id'
        );
    }

    public function related(): BelongsToMany
    {
        return $this->belongsToMany(
            __CLASS__,
            'okapi_relationship_instance',
            'okapi_from_instance_id',
            'okapi_to_instance_id')->withPivot('okapi_relationship_id');

    }

    public function reverse_related(): BelongsToMany
    {
        return $this->belongsToMany(
            __CLASS__,
            'okapi_relationship_instance',
            'okapi_to_instance_id',
            'okapi_from_instance_id')->withPivot('okapi_relationship_id');

    }

    public function relationships(): HasMany
    {
        return $this->hasMany(
            Relationship::class,
            'okapi_type_from_id',
            'id',
        );
    }

    public function reverse_relationships(): HasMany
    {
        return $this->hasMany(
            Relationship::class,
            'okapi_type_to_id',
            'id',
        );
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(
            Type::class,
            'okapi_type_id',
            'id',
        );
    }
}
