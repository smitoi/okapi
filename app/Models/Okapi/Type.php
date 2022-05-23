<?php

namespace App\Models\Okapi;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Models\Permission;

class Type extends Model
{
    public const PERMISSIONS = [
        'list',
        'view',
        'create',
        'edit',
        'delete',
    ];

    protected $table = 'okapi_types';

    protected $fillable = [
        'name',
        'slug',
        'is_collection',
        'ownable',
        'private',
        'type'
    ];

    protected $casts = [
        'is_collection' => 'bool',
        'ownable' => 'bool',
        'private' => 'bool',
    ];

    public function fields(): HasMany
    {
        return $this->hasMany(Field::class, 'okapi_type_id', 'id');
    }

    public function relationships(): HasMany
    {
        return $this->hasMany(Relationship::class, 'okapi_type_from_id', 'id');
    }

    public function reverse_relationships(): HasMany
    {
        return $this->hasMany(Relationship::class, 'okapi_type_to_id', 'id');
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class, 'okapi_type_id', 'id');
    }
}
