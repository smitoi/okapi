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

    public function getRequestRulesArrayForRelationship(Relationship $relationship, ?Instance $instance = null, bool $reverse = false): array
    {
        if ($instance) {
            $formattedRules = ['sometimes',];
        }

        $formattedRules[] = 'nullable';
        $related = $relationship->toType;

        if ($reverse) {
            $singularRelationship = $relationship->type === 'has one' || $relationship->type === 'has many';
            $multipleRelationship = $relationship->type === 'belongs to one' || $relationship->type === 'belongs to many';
            $tableName = TypeService::getTableNameForType($relationship->fromType);
        } else {
            $singularRelationship = $relationship->type === 'has one' || $relationship->type === 'belongs to one';
            $multipleRelationship = $relationship->type === 'has many' || $relationship->type === 'belongs to many';
            $tableName = TypeService::getTableNameForType($related);
        }

        if ($singularRelationship) {
            $formattedRules[] = 'integer';
            $formattedRules[] = Rule::exists($tableName, 'id');
        } elseif ($multipleRelationship) {
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
            $allRules[TypeService::getForeignKeyNameForRelationship($relationship)] =
                $this->getRequestRulesArrayForRelationship($relationship);
        }

        foreach ($type->reverseRelationships as $relationship) {
            $allRules[TypeService::getReverseForeignKeyNameForRelationship($relationship)] =
                $this->getRequestRulesArrayForRelationship($relationship, reverse: true);
        }

        return $allRules;
    }
}
