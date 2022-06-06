<?php

namespace App\Models\Okapi;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasPermissions;

/**
 * @property int $id
 * @property string $name
 *
 * Class ApiKey
 * @package App\Models\Okapi
 */
class ApiKey extends Model
{
    use HasPermissions;

    public const API_KEY_LENGTH = 16;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'token',
    ];
}
