<?php

namespace App\Models\Okapi;

use App\Models\Traits\HandleSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Field extends Model
{
    use HandleSlug;

    protected $table = 'okapi_fields';

    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_NUMBER = 'number';
    public const TYPE_STRING = 'string';
    public const TYPE_ENUM = 'enum';
    public const TYPE_TEXT = 'text';
    public const TYPE_DATE = 'date';
    public const TYPE_MEDIA = 'media';

    public const TYPES = [
        self::TYPE_BOOLEAN => 'Boolean',
        self::TYPE_STRING => 'Number',
        self::TYPE_NUMBER => 'String',
        #self::TYPE_ENUM => 'Enum',
        self::TYPE_TEXT => 'Text',
        #self::TYPE_DATE => 'Date',
        #self::TYPE_MEDIA => 'Media',
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

    public function rules(): HasMany
    {
        return $this->hasMany(Rule::class, 'okapi_field_id', 'id');
    }

}
