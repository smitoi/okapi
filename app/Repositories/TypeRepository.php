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
use Spatie\Permission\Models\Permission;

class TypeRepository
{
    private function getRelationshipDetails(Relationship $relationship, bool $reverse = false): array
    {
        if ($reverse) {
            $displayField = $relationship->reverse_display_field()->first();
            $instances = $relationship->reverse_instances()->get();
        } else {
            $displayField = $relationship->display_field()->first();
            $instances = $relationship->instances()->get();
        }

        $relationshipOptions = [];
        /** @var Instance $instance */
        foreach ($instances as $instance) {
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

        return $relationshipOptions;
    }

    public function getRelationshipsWithOptions(Type $type): Collection
    {
        $relationships = $type->relationships()->get();

        /** @var Relationship $relationship */
        foreach ($relationships as $relationship) {
            $relationship->setAttribute('options', $this->getRelationshipDetails($relationship));
        }

        $reverseRelationships = $type->reverse_relationships()->get();
        /** @var Relationship $relationship */
        foreach ($reverseRelationships as $relationship) {
            $relationship->setAttribute('options', $this->getRelationshipDetails($relationship, true));
        }

        return $relationships->merge($reverseRelationships);
    }

    public function createType(array $validated): void
    {
        DB::transaction(static function () use ($validated) {
            /** @var Type $type */
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

                $validatedRelationship['reverse_okapi_field_display_id'] = Field::query()
                    ->where('name', '=', $validatedRelationship['reverse_okapi_field_display_name'] ?? null)
                    ->first();
                Relationship::query()->create($validatedRelationship);
            }

            foreach (Type::PERMISSIONS as $permission) {
                Permission::query()->create([
                    'name' => "okapi-type-{$type->id}.$permission",
                    'okapi_type_id' => $type->id,
                ]);
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
