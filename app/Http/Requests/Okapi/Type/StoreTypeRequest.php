<?php

namespace App\Http\Requests\Okapi\Type;

use App\Models\Okapi\Field;
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
            'ownable' => 'boolean',
            'private' => 'boolean',
            'is_collection' => 'boolean',
            'fields' => 'required|array',
            'fields.*.name' => 'required',
            'fields.*.type' => 'required',
            'fields.*.dashboard_visible' => 'boolean',
            'fields.*.api_visible' => 'boolean',
            'fields.*.rules' => 'array',
            'fields.*.options' => 'sometimes|array',
            'relationships' => 'sometimes|exclude_if:is_collection,false',
            'relationships.*.type' => 'required',
            'relationships.*.api_visibility' => 'required|numeric',
            'relationships.*.okapi_type_to_id' => 'required|exists:okapi_types,id',
            'relationships.*.okapi_field_display_id' => 'sometimes|exists:okapi_fields,id',
        ];
    }
}
