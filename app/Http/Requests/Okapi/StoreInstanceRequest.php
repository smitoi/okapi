<?php

namespace App\Http\Requests\Okapi;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use JsonException;

class StoreInstanceRequest extends FormRequest
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
     * @throws JsonException
     */
    public function rules(): array
    {
        $fields = $this->route('type')->fields->load('rules');
        $allRules = [];
        foreach ($fields as $field) {
            if ($field->type === 'number') {
                $formattedRules = [
                    'numeric'
                ];
            } else {
                $formattedRules = [];
            }

            foreach ($field->rules as $rule) {
                if ($rule->name === 'unique') {
                    $formattedRules[] = Rule::unique('okapi_instance_field', 'value')->where(function ($query) use ($field) {
                        return $query->where('okapi_field_id', $field->id);
                    });
                } elseif (in_array($rule->name, ['accepted', 'declined', 'required'])) {
                    $formattedRules[] = $rule->name;
                } else {
                    $formattedRules[] = $rule->name . ':' .
                        json_decode($rule->properties, false, 512, JSON_THROW_ON_ERROR)->value;
                }
            }
            $allRules[$field->slug] = $formattedRules;
        }

        return $allRules;
    }
}
