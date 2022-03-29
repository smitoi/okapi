<?php

namespace App\Models\Okapi;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Instance extends Model
{
    protected $table = 'okapi_instances';

    protected $fillable = [
        'okapi_type_id',
    ];

    public function values(): HasMany
    {
        return $this->hasMany(InstanceField::class, 'okapi_instance_id', 'id');
    }

    public function fields(): BelongsToMany
    {
        return $this->belongsToMany(
            Field::class,
            'okapi_instance_field',
            'okapi_instance_id',
            'okapi_field_id'
        );
    }

    public function related(): HasMany
    {
        return $this->hasMany(RelationshipInstance::class, 'okapi_from_instance_id', 'id');
    }

    public function relationships(): BelongsToMany
    {
        return $this->belongsToMany(
            Relationship::class,
            'okapi_relationship_instance',
            'okapi_from_instance_id',
            'okapi_relationship_id',
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
