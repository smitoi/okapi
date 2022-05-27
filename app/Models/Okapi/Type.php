<?php

namespace App\Models\Okapi;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property boolean $is_collection
 * @property boolean $ownable
 * @property boolean $private
 * @property string $type
 *
 * @property Collection $fields
 * @property Collection $relationships
 *
 * Class Type
 * @package App\Models\Okapi
 */
class Type extends Model
{
    public const PERMISSIONS = [
        'index' => 'list',
        'show' => 'view',
        'store' => 'create',
        'update' => 'edit',
        'destroy' => 'delete',
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

    public function permissions(): MorphToMany
    {
        return $this->morphedByMany(Permission::class, 'target');
    }
}
