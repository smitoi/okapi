<?php

namespace App\Services;

use App\Models\Okapi\Field;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;

class RuleService
{
    public function getRequestRulesArrayForField(Field $field): array
    {
        $formattedRules = [
            'nullable',
        ];

        if ($field->type === 'number') {
            $formattedRules[] = 'numeric';
        }

        foreach ($field->rules as $rule) {
            if ($rule->name === 'unique') {
                $formattedRules[] = Rule::unique('okapi_instance_field', 'value')->where(function ($query) use ($field) {
                    return $query->where('okapi_field_id', $field->id);
                });
            } elseif (in_array($rule->name, ['accepted', 'declined', 'required'])) {
                if ($rule->name === 'required') {
                    unset($formattedRules[array_search('nullable', $formattedRules)]);
                }

                $formattedRules[] = $rule->name;
            } else {
                $formattedRules[] = $rule->name . ':' .
                    $rule->properties->value;
            }
        }

        return $formattedRules;
    }

    public function getRequestRulesArrayForFields(Collection $fields): array
    {
        $allRules = [];
        foreach ($fields as $field) {
            $allRules[$field->slug] = $this->getRequestRulesArrayForField($field);
        }

        return $allRules;
    }
}
