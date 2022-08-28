<?php

namespace App\Http\Requests\Okapi\Role;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'unique:roles,name',
            ],
            'slug' => [
                'required',
                'unique:roles,slug',
            ],
            'permissions' => 'array|exists:permissions,id',
            'api_register' => 'boolean',
            'api_login' => 'boolean',
        ];
    }
}
