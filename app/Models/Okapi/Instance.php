<?php

namespace App\Models\Okapi;

use App\Services\TypeService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property int $created_by
 *
 * @property Type $type
 *
 * Class Instance
 * @package App\Models\Okapi
 */
class Instance extends Model
{
    protected $table = '';
    protected $guarded = [];

    public static function queryForType(Type $type): Builder
    {
        return parent::query()->from(TypeService::getTableNameForType(
            $type,
        ));
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

    public function type(): BelongsTo
    {
        return $this->belongsTo(
            Type::class,
            'okapi_type_id',
            'id',
        );
    }
}
