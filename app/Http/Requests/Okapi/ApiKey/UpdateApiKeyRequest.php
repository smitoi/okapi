<?php

namespace App\Http\Requests\Okapi\ApiKey;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateApiKeyRequest extends FormRequest
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
                Rule::unique('api_keys', 'name')
                    ->ignore($this->route('api_key')->id, 'id'),
            ],
            'permissions' => 'array|exists:permissions,id',
        ];
    }
}
