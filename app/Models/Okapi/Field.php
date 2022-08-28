<?php

namespace App\Models\Okapi;

use App\Models\Traits\HandleSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $type
 * @property boolean $dashboard_visible
 * @property boolean $api_visible
 * @property object $properties
 * @property int $okapi_type_id
 *
 * Class Field
 * @package App\Models\Okapi
 */
class Field extends Model
{
    use HandleSlug;

    protected array $slugColumns = [
        'name' => 'slug',
    ];

    protected $table = 'okapi_fields';

    protected $fillable = [
        'name',
        'slug',
        'type',
        'properties',
        'okapi_type_id',
        'dashboard_visible',
        'api_visible',
    ];

    protected $casts = [
        'properties' => 'object',
        'dashboard_visible' => 'boolean',
        'api_visible' => 'boolean',
    ];

    public function okapiType(): BelongsTo
    {
        return $this->belongsTo(Type::class, 'okapi_type_id', 'id');
    }
}
