<?php

namespace App\Http\Requests\Okapi\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
                Rule::unique('users', 'email')
                    ->ignore($this->route('user')->id, 'id'),
                'email',
            ],
            'password' => 'nullable|confirmed',
            'roles' => 'array',
        ];
    }
}
