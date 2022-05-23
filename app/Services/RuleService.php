<?php

namespace App\Services;

use App\Models\Okapi\Field;
use App\Models\Okapi\Instance;
use App\Models\Okapi\Relationship;
use App\Models\Okapi\Type;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;

class RuleService
{
    private function getRequestRulesArrayForField(Field $field, ?Instance $instance = null): array
    {
        if ($instance) {
            $formattedRules = ['sometimes',];
        }

        $formattedRules[] = 'nullable';


        if ($field->type === 'number') {
            $formattedRules[] = 'numeric';
        } elseif ($field->type === 'enum') {
            $formattedRules[] = Rule::in($field->properties->options);
        } elseif ($field->type === 'file') {
            $formattedRules[] = 'file';
        }

        /** @var Rule $rule */
        foreach ($field->rules()->get() as $rule) {
            if ($rule->name === 'unique') {
                $formattedRules[] = Rule::unique('okapi_instance_field', 'value')
                    ->ignore($instance?->id, 'id')
                    ->where(function ($query) use ($field) {
                        return $query->where('okapi_field_id', $field->id);
                    });
            } elseif (in_array($rule->name, ['accepted', 'declined', 'required'])) {
                if ($rule->name === 'required') {
                    unset($formattedRules[array_search('nullable', $formattedRules, true)]);
                }

                $formattedRules[] = $rule->name;
            } else {
                $formattedRules[] = $rule->name . ':' .
                    $rule->value;
            }
        }

        return $formattedRules;
    }

    public function getRequestRulesArrayForRelationship(Relationship $relationship): array
    {
        return [
            'nullable',
            'array',
            Rule::exists('okapi_instances', 'id')
                ->where('okapi_type_id', $relationship->getAttribute('okapi_type_to_id')),
        ];
    }

    public function getRequestRulesArrayForFields(Type $type, ?Instance $instance = null): array
    {
        $fields = $type->fields->load('rules');
        $relationships = $type->relationships;

        $allRules = [];
        foreach ($fields as $field) {
            $allRules[$field->slug] = $this->getRequestRulesArrayForField($field, $instance);
        }

        foreach ($relationships as $relationship) {
            $allRules[$relationship->slug] = $this->getRequestRulesArrayForRelationship($relationship);
        }

        return $allRules;
    }
}
