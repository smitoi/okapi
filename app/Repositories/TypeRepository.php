<?php

namespace App\Repositories;

use App\Models\Okapi\Field;
use App\Models\Okapi\Instance;
use App\Models\Okapi\InstanceField;
use App\Models\Okapi\Relationship;
use App\Models\Okapi\Rule;
use App\Models\Okapi\Type;
use App\Services\TypeService;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class TypeRepository
{
    protected TypeService $typeService;

    public function __construct(TypeService $typeService)
    {
        $this->typeService = $typeService;
    }

    private function getRelationshipDetails(Relationship $relationship): array
    {
        /** @var Field $displayField */
        $displayField = $relationship->displayField()->first();

        $displayField = $displayField->slug ?? 'id';
        /** @var Type $related */
        $related = $relationship->toType()->firstOrFail();
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

    public function getRelationshipsWithOptions(Type $type): Collection
    {
        $relationships = $type->relationships()->with('toType')->get();

        /** @var Relationship $relationship */
        foreach ($relationships as $relationship) {
            $relationship->setAttribute('options', $this->getRelationshipDetails($relationship));
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

                    foreach (Type::PERMISSIONS as $permission) {
                        Permission::query()->create([
                            'name' => "okapi-type-$type->id.$permission",
                            'target_type' => Type::class,
                            'target_id' => $type->id,
                        ]);
                    }
                }
            }

            return $type;
        });

        $deletedFields = $type->fields()->whereNotIn('id',
            collect($validated['fields'])
                ->filter(fn($field) => isset($field['id']))
                ->map(fn($field) => $field['id'])
                ->toArray()
        )->get();

        $deletedRelationships = $type->relationships()->whereNotIn('id',
            collect($validated['relationships'])
                ->filter(fn($field) => isset($field['id']))
                ->map(fn($field) => $field['id'])
                ->toArray()
        )->get();

        $this->typeService->cleanLeftoverFields($type, $deletedFields);
        $this->typeService->cleanLeftoverRelationships($type, $deletedRelationships);
        Field::query()->whereIn('id', $deletedFields->map(fn($field) => $field->id));
        Relationship::query()->whereIn('id', $deletedRelationships->map(fn($relationship) => $relationship->id));
        $this->typeService->updateTableUsingType($type, $newFields, $newRelationships);
    }
}
