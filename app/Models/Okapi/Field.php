<?php

namespace App\Models\Okapi;

use App\Models\Traits\HandleSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Field extends Model
{
    use HandleSlug;

    protected $table = 'okapi_fields';

    public const TYPES = [
        'boolean' => 'Boolean',
        'number' => 'Number',
        'string' => 'String',
        'enum' => 'Enum',
        'text' => 'Text',
        'date' => 'Date',
        'media' => 'Media',
    ];

    protected $fillable = [
        'name',
        'type',
        'okapi_type_id',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class, 'okapi_type_id', 'id');
    }
}
