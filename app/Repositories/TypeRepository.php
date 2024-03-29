<?php

namespace App\Repositories;

use App\Models\Okapi\Field;
use App\Models\Okapi\Instance;
use App\Models\Okapi\Relationship;
use App\Models\Okapi\Type;
use App\Services\TypeService;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class TypeRepository
{
    protected TypeService $typeService;

    public function __construct(TypeService $typeService)
    {
        $this->typeService = $typeService;
    }

    private function getRelationshipDetails(Relationship $relationship, bool $reverse = false): array
    {
        if ($reverse) {
            $displayField = $relationship->reverseDisplayField;
        } else {
            $displayField = $relationship->displayField;
        }

        $displayField = $displayField->slug ?? 'id';

        if ($reverse) {
            $related = $relationship->fromType;
        } else {
            $related = $relationship->toType;
        }

        $instances = Instance::queryForType($related)->get();

        $relationshipOptions = [];
        /** @var Instance $instance */
        foreach ($instances as $instance) {
            $relationshipOptions[] = [
                'label' => $instance->{$displayField},
                'value' => $instance->id,
            ];
        }

        return $relationshipOptions;
    }

    public function getRelationshipsWithOptions(Type $type, bool $reverse = false): Collection
    {
        if ($reverse) {
            $relationships = $type->reverseRelationships;
        } else {
            $relationships = $type->relationships;
        }

        /** @var Relationship $relationship */
        foreach ($relationships as $relationship) {
            $relationship->setAttribute('options', $this->getRelationshipDetails($relationship, $reverse));
            $relationship->setAttribute('key', $reverse ? TypeService::getReverseForeignKeyNameForRelationship($relationship) :
                TypeService::getForeignKeyNameForRelationship($relationship));
        }

        return $relationships;
    }

    public function createType(array $validated): void
    {
        $type = DB::transaction(static function () use ($validated) {
            /** @var Type $type */
            $type = Type::query()->create(Arr::except($validated, ['fields']));

            foreach ($validated['fields'] as $validatedField) {
                $validatedField['okapi_type_id'] = $type->id;

                $validatedField['properties'] = [
                    'rules' => [],
                    'options' => $validatedField['options'] ?? [],
                ];

                foreach ($validatedField['rules'] as $ruleKey => $ruleValue) {
                    $validatedField['properties']['rules'][$ruleKey] = $ruleValue;
                }

                Field::query()->create(Arr::except($validatedField, ['fields', 'relationships']));
            }

            foreach ($validated['relationships'] ?? [] as $validatedRelationship) {
                $validatedRelationship['okapi_type_from_id'] = $type->getAttribute('id');
                /** @var Field $field */
                $field = Field::query()
                    ->where('okapi_type_id', $type->id)
                    ->where('name', '=', $validatedRelationship['reverse_okapi_field_display_name'] ?? null)
                    ->first();
                $validatedRelationship['reverse_okapi_field_display_id'] = $field?->id;

                Relationship::query()->create($validatedRelationship);
            }

            foreach (Type::PERMISSIONS as $permission) {
                Permission::query()->create([
                    'name' => "okapi-type-$type->id.$permission",
                    'target_type' => Type::class,
                    'target_id' => $type->id,
                ]);
            }

            return $type;
        });

        if ($type) {
            $this->typeService->createTableUsingType($type);
        }
    }

    public function updateType(array $validated, Type $type): void
    {
        $newFields = collect([]);
        $newRelationships = collect([]);
        $type = DB::transaction(static function () use ($validated, $type, $newFields, $newRelationships) {
            $type->update(Arr::except($validated, ['fields', 'relationships']));

            foreach ($validated['fields'] as $validatedField) {
                $validatedField['properties'] = [
                    'rules' => [],
                    'options' => $validatedField['options'] ?? [],
                ];

                foreach ($validatedField['rules'] as $ruleKey => $ruleValue) {
                    $validatedField['properties']['rules'][$ruleKey] = $ruleValue;
                }

                /** @var Field $field */
                if (isset($validatedField['id'])) {
                    $field = Field::query()
                        ->where('id', $validatedField['id'])
                        ->firstOrFail();
                    $field->update($validatedField);
                } else {
                    $validatedField['okapi_type_id'] = $type->getAttribute('id');
                    $newFields->add(Field::query()->create(Arr::except($validatedField, ['fields', 'relationships'])));
                }
            }

            foreach ($validated['relationships'] ?? [] as $validatedRelationship) {
                if (isset($validatedRelationship['id'])) {
                    $relationship = Relationship::query()
                        ->where('id', $validatedRelationship['id'])
                        ->firstOrFail();
                    $relationship->update($validatedRelationship);
                } else {
                    $validatedRelationship['okapi_type_from_id'] = $type->getAttribute('id');
                    $newRelationships->add(Relationship::query()->create($validatedRelationship));
                }
            }

            return $type;
        });

        $deletedFields = $type->fields()->whereNotIn('id',
            collect($validated['fields'])
                ->filter(fn($field) => isset($field['id']))
                ->map(fn($field) => $field['id'])
                ->toArray()
        )->whereNotIn('id', $newFields->map(fn($field) => $field->id))->get();

        $deletedRelationships = $type->relationships()->whereNotIn('id',
            collect($validated['relationships'])
                ->filter(fn($relationship) => isset($relationship['id']))
                ->map(fn($relationship) => $relationship['id'])
                ->toArray()
        )->whereNotIn('id', $newRelationships->map(fn($relationship) => $relationship->id))->get();

        $this->typeService->cleanLeftoverFields($type, $deletedFields);
        $this->typeService->cleanLeftoverRelationships($type, $deletedRelationships);
        Field::query()
            ->whereIn('id', $deletedFields->map(fn($field) => $field->id))
            ->delete();
        Relationship::query()
            ->whereIn('id', $deletedRelationships->map(fn($relationship) => $relationship->id))
            ->delete();
        $this->typeService->updateTableUsingType($type, $newFields, $newRelationships);
    }

    public function deleteType(Type $type): void
    {
        $this->typeService->cleanLeftoverFields($type, $type->fields);
        $this->typeService->cleanLeftoverRelationships($type, $type->relationships);
        Permission::query()
            ->where('target_id', $type->id)
            ->where('target_type', Type::class)
            ->delete();
        $this->typeService->dropTableForType($type);
        $type->delete();
    }
}
