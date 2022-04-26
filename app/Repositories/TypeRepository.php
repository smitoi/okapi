<?php

namespace App\Repositories;

use App\Models\Okapi\Field;
use App\Models\Okapi\Instance;
use App\Models\Okapi\InstanceField;
use App\Models\Okapi\Relationship;
use App\Models\Okapi\Rule;
use App\Models\Okapi\Type;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class TypeRepository
{
    public function getRelationshipsWithOptions(Type $type): Collection
    {
        $relationships = $type->relationships()->get();

        /** @var Relationship $relationship */
        foreach ($relationships as $relationship) {
            $displayField = $relationship->display_field()->first();

            $relationshipOptions = [];
            /** @var Instance $instance */
            foreach ($relationship->instances()->get() as $instance) {
                $storeValue = $instance->getAttribute('id');

                if ($displayField) {
                    $instanceField = InstanceField::query()
                        ->where('okapi_field_id', $displayField->id)
                        ->where('okapi_instance_id', $instance->getAttribute('id'))
                        ->first();

                    if ($instanceField) {
                        $displayValue = $instanceField->getAttribute('value');
                    } else {
                        $displayValue = InstanceField::EMPTY_DISPLAY_VALUE;
                    }
                } else {
                    $displayValue = $instance->getAttribute('id');
                }

                $relationshipOptions[] = [
                    'label' => $displayValue,
                    'value' => $storeValue,
                ];
            }

            $relationship->setAttribute('options', $relationshipOptions);
        }

        return $relationships;
    }

    public function createType(array $validated): void
    {
        DB::transaction(static function () use ($validated) {
            /** @var Type $contentType */
            $type = Type::query()->create(Arr::except($validated, ['fields']));

            foreach ($validated['fields'] as $validatedField) {
                $validatedField['okapi_type_id'] = $type->getAttribute('id');

                $field = Field::query()->create($validatedField);

                foreach ($validatedField['rules'] as $ruleKey => $ruleValue) {
                    if ($ruleValue) {
                        Rule::query()->create([
                            'name' => $ruleKey,
                            'value' => $ruleValue,
                            'okapi_field_id' => $field->getAttribute('id'),
                        ]);
                    }
                }
            }

            foreach ($validated['relationships'] as $validatedRelationship) {
                $validatedRelationship['okapi_type_from_id'] = $type->getAttribute('id');

                $validatedRelationship['okapi_type_to_id'] = $validatedRelationship['to'];
                $validatedRelationship['okapi_field_display_id'] = $validatedRelationship['display'] ?? null;
                unset($validatedRelationship['to'], $validatedRelationship['store'], $validatedRelationship['display']);

                Relationship::query()->create($validatedRelationship);
            }
        });
    }

    public function updateType(array $validated, Type $type): void
    {
        DB::transaction(static function () use ($validated, $type) {
            $type->update(Arr::except($validated, ['fields']));

            $type->fields()->whereNotIn('id',
                collect($validated['fields'])
                    ->filter(fn($field) => isset($field['id']))
                    ->map(fn($field) => $field['id'])
                    ->toArray()
            )->delete();

            $type->relationships()->whereNotIn('id',
                collect($validated['relationships'])
                    ->filter(fn($field) => isset($field['id']))
                    ->map(fn($field) => $field['id'])
                    ->toArray()
            )->delete();

            foreach ($validated['fields'] as $validatedField) {
                if (isset($validatedField['id'])) {
                    $field = Field::query()
                        ->where('id', $validatedField['id'])
                        ->firstOrFail();
                    $field->update(Arr::except($validatedField, ['id', 'rules']));
                } else {
                    $validatedField['okapi_type_id'] = $type->getAttribute('id');
                    $field = Field::query()->create(Arr::except($validatedField, ['rules']));
                }

                /** @var Field $field */
                foreach ($validatedField['rules'] as $ruleKey => $ruleValue) {
                    $rule = Rule::query()
                        ->where('okapi_field_id', $field->id)
                        ->where('name', $ruleKey)->first();

                    if ($ruleValue) {
                        if ($rule) {
                            $rule->update([
                                'value' => $ruleValue
                            ]);
                        } else {
                            Rule::query()->create([
                                'name' => $ruleKey,
                                'properties' => [
                                    'value' => $ruleValue,
                                ],
                                'okapi_field_id' => $field->getAttribute('id'),
                            ]);
                        }
                    } elseif ($rule) {
                        $rule->delete();
                    }
                }
            }

            foreach ($validated['relationships'] ?? [] as $validatedRelationship) {
                $validatedRelationship['okapi_type_to_id'] = $validatedRelationship['to'];
                $validatedRelationship['okapi_field_display_id'] = $validatedRelationship['display'] ?? null;
                unset($validatedRelationship['to'], $validatedRelationship['store'], $validatedRelationship['display']);

                if (isset($validatedRelationship['id'])) {
                    $relationship = Relationship::query()
                        ->where('id', $validatedRelationship['id'])
                        ->firstOrFail();
                    $relationship->update(Arr::except($validatedRelationship, ['id']));
                } else {
                    $validatedRelationship['okapi_type_from_id'] = $type->getAttribute('id');
                    Relationship::query()->create($validatedRelationship);
                }
            }
        });
    }
}
