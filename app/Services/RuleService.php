<?php

namespace App\Services;

use App\Models\Okapi\Field;
use App\Models\Okapi\Relationship;
use App\Models\Okapi\Type;
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

    public function getRequestRulesArrayForRelationship(Relationship $relationship): array
    {
        return [
            Rule::exists('okapi_instances', 'id')
                ->where('okapi_type_id', $relationship->getAttribute('okapi_type_to_id')),
        ];
    }

    public function getRequestRulesArrayForFields(Type $type): array
    {
        $fields = $type->fields->load('rules');
        $relationships = $type->relationships;

        $allRules = [];
        foreach ($fields as $field) {
            $allRules[$field->slug] = $this->getRequestRulesArrayForField($field);
        }

        foreach ($relationships as $relationship) {
            $allRules[$relationship->slug] = $this->getRequestRulesArrayForRelationship($relationship);
        }

        return $allRules;
    }
}
