<?php

namespace App\Models\Okapi;

use App\Models\Traits\HandleSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Field extends Model
{
    use HandleSlug;

    protected array $slugColumns = [
        'name' => 'slug',
    ];

    protected $table = 'okapi_fields';

    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_NUMBER = 'number';
    public const TYPE_STRING = 'string';
    public const TYPE_ENUM = 'enum';
    public const TYPE_DATE = 'date';
    public const TYPE_HOUR = 'hour';
    public const TYPE_FILE = 'file';

    public const TYPES = [
        self::TYPE_BOOLEAN => 'Boolean',
        self::TYPE_STRING => 'String',
        self::TYPE_NUMBER => 'Number',
        self::TYPE_ENUM => 'Enum',
        self::TYPE_FILE => 'File',
        self::TYPE_DATE => 'Date',
        self::TYPE_HOUR => 'Hour'
    ];

    protected $fillable = [
        'name',
        'type',
        'properties',
        'okapi_type_id',
    ];

    protected $casts = [
        'properties' => 'object',
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
