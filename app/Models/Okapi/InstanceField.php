<?php

namespace App\Models\Okapi;

use Illuminate\Database\Eloquent\Model;

class InstanceField extends Model
{
    public const EMPTY_DISPLAY_VALUE = '< null >';

    protected $table = 'okapi_instance_field';
}
