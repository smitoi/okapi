<?php

namespace App\Http\Requests\Okapi\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            'name' => 'required|min:3|max:32',
            'email' => [
                'required',
                'unique:users',
                'email',
            ],
            'password' => 'required|confirmed',
            'roles' => 'array',
        ];
    }
}
