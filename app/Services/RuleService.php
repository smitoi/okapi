<?php

namespace App\Services;

use App\Models\Okapi\Field;
use App\Models\Okapi\Instance;
use App\Models\Okapi\Relationship;
use App\Models\Okapi\Type;
use Illuminate\Validation\Rule;

class RuleService
{
    private function getRequestRulesArrayForField(Type $type, Field $field, ?Instance $instance = null): array
    {
        if ($instance) {
            $formattedRules = ['sometimes',];
        }

        $formattedRules[] = 'nullable';

        if ($field->type === 'number' || $field->type === 'double') {
            $formattedRules[] = 'numeric';
        } elseif ($field->type === 'enum') {
            $formattedRules[] = Rule::in($field->properties->options);
        } elseif ($field->type === 'file') {
            $formattedRules[] = 'file';
        } elseif ($field->type === 'email') {
            $formattedRules[] = 'email';
        }

        foreach ($field->properties->rules as $rule => $value) {
            if (!is_bool($value) || $value) {
                if ($rule === 'unique') {
                    $formattedRules[] = Rule::unique(TypeService::getTableNameForType($type), $field->slug)
                        ->ignore($instance?->id, 'id');
                } elseif ($rule === 'required') {
                    unset($formattedRules[array_search('nullable', $formattedRules, true)]);
                } elseif (in_array($rule, ['accepted', 'declined'])) {
                    $formattedRules[] = $rule;
                } else {
                    $formattedRules[] = "$rule:$value";
                }
            }
        }

        return $formattedRules;
    }

    public function getRequestRulesArrayForRelationship(Relationship $relationship, ?Instance $instance = null): array
    {
        if ($instance) {
            $formattedRules = ['sometimes',];
        }

        $formattedRules[] = 'nullable';
        /** @var Type $related */
        $related = $relationship->toType()->get();

        if ($relationship->type === 'has one' || $relationship->type === 'belongs to one') {
            $formattedRules[] = 'integer';
            $formattedRules[] = [
                Rule::exists(TypeService::getTableNameForType($related), 'id')
            ];
        } elseif ($relationship->type === 'has many' || $relationship->type === 'belongs to many') {
            $formattedRules[] = 'array';
        }

        return $formattedRules;
    }

    public function getRequestRulesArrayForFields(Type $type, ?Instance $instance = null): array
    {
        $allRules = [];

        foreach ($type->fields as $field) {
            $allRules[$field->slug] = $this->getRequestRulesArrayForField($type, $field, $instance);
        }

        foreach ($type->relationships as $relationship) {
            /** @var Type $related */
            $related = $relationship->toType()->get();
            $allRules[TypeService::getTableNameForType($related)] =
                $this->getRequestRulesArrayForRelationship($relationship);
        }

        return $allRules;
    }
}
