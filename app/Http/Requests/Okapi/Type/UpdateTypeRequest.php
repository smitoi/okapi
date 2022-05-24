<?php

namespace App\Http\Requests\Okapi\Type;

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
            'ownable' => 'boolean',
            'private' => 'boolean',
            'fields' => 'required',
            'fields.*.id' => 'sometimes|exists:okapi_fields',
            'fields.*.name' => 'required',
            'fields.*.type' => 'required',
            'fields.*.rules' => 'sometimes|array',
            'fields.*.properties' => 'sometimes',
            'relationships' => 'sometimes|exclude_if:is_collection,false',
            'relationships.*.name' => 'required',
            'relationships.*.type' => 'required',
            'relationships.*.has_reverse' => 'boolean',
            'relationships.*.reverse_name' => 'exclude_if:has_reverse,true|nullable',
            'relationships.*.okapi_type_to_id' => 'required|exists:okapi_types,id',
            'relationships.*.okapi_field_display_id' => 'sometimes|exists:okapi_fields,id',
            'relationships.*.reverse_okapi_field_display_id' => 'exclude_if:has_reverse,true|nullable|exists:okapi_fields,id',
        ];
    }
}
