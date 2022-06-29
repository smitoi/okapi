<?php

namespace App\Models\Okapi;

use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Spatie\Permission\Traits\HasPermissions;

/**
 * @property int $id
 * @property string $name
 * @property string $token
 *
 * Class ApiKey
 * @package App\Models\Okapi
 */
class ApiKey extends Model implements AuthorizableContract
{
    use HasPermissions, Authorizable;

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
