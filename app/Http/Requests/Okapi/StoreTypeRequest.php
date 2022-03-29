<?php

namespace App\Http\Requests\Okapi;

use Illuminate\Foundation\Http\FormRequest;

class StoreTypeRequest extends FormRequest
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
                'unique:okapi_types,name',
            ],
            'slug' => [
                'required',
                'unique:okapi_types,slug',
            ],
            'is_collection' => 'boolean',
            'fields' => 'required',
            'fields.*.name' => 'required',
            'fields.*.type' => 'required',
            'fields.*.rules' => 'required',
            'relationships' => 'sometimes',
            'relationships.*.name' => 'required',
            'relationships.*.reverse_name' => 'required',
            'relationships.*.type' => 'required',
            'relationships.*.display' => 'sometimes|exists:okapi_fields,id',
        ];
    }
}
