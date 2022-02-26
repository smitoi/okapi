<?php

namespace App\Http\Requests\Okapi;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTypeRequest extends FormRequest
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
                Rule::unique('okapi_types', 'name')
                    ->ignore($this->route('type')->id, 'id')
            ],
            'slug' => [
                'required',
                Rule::unique('okapi_types', 'slug')
                    ->ignore($this->route('type')->id, 'id')
            ],
            'is_collection' => 'boolean',
            'fields' => 'required',
            'fields.*.name' => 'required',
            'fields.*.type' => 'required',
        ];
    }
}
