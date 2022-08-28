<?php

namespace App\Http\Requests\Okapi\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
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
                Rule::unique('roles', 'name')
                    ->ignore($this->route('role')->id, 'id')
            ],
            'slug' => [
                'required',
                Rule::unique('roles', 'slug')
                    ->ignore($this->route('role')->id, 'id')
            ],
            'permissions' => 'array|exists:permissions,id',
            'api_register' => 'boolean',
            'api_login' => 'boolean',
        ];
    }
}
