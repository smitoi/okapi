<?php

namespace App\Repositories;

use App\Models\Okapi\Instance;
use App\Models\Okapi\Type;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InstanceRepository
{
    private function treatValidatedInstanceInput(array $validated,
                                                 array $fields,
                                                 array $relationships,
                                                 array $reverseRelationships,
                                                 Instance $instance): void
    {
        $validated = array_filter($validated, static fn($item) => !empty($item));

        $fieldsData = $relationshipsData = $reverseRelationshipsData = [];
        $touchedRelationships = $touchedReverseRelationships = [];

        foreach ($validated as $key => $value) {
            if (array_key_exists($key, $fields)) {
                if ($value instanceof UploadedFile) {
                    $value = $value->store($instance->id, 'public');
                }

                $fieldsData[$fields[$key]] = [
                    'value' => $value
                ];
            } else {
                if (!is_array($value)) {
                    $value = [$value];
                }

                if (Arr::exists($relationships, $key)) {
                    foreach ($value as $related) {
                        $relationshipsData[$related] = [
                            'okapi_relationship_id' => $relationships[$key],
                        ];
                    }
                    $touchedRelationships[] = $relationships[$key];
                } elseif (Arr::exists($reverseRelationships, $key)) {
                    foreach ($value as $related) {
                        $reverseRelationshipsData[$related] = [
                            'okapi_relationship_id' => $reverseRelationships[$key],
                        ];
                    }
                    $touchedReverseRelationships[] = $reverseRelationships[$key];
                }
            }
        }

        $instance->fields()->sync($fieldsData);
        $instance->related()->wherePivotIn('okapi_relationship_id', $touchedRelationships)->detach();
        $instance->reverse_related()->wherePivotIn('okapi_relationship_id', $touchedReverseRelationships)->detach();
        $instance->related()->sync($relationshipsData, $relationshipsData);
        $instance->reverse_related()->sync($reverseRelationshipsData, $reverseRelationshipsData);
    }

    public function createInstance(array $validated, Type $type): Instance
    {
        $self = $this;
        return DB::transaction(static function () use ($validated, $type, $self) {
            /** @var Instance $instance */
            $instance = Instance::query()->create([
                'okapi_type_id' => $type->id,
                'user_id' => Auth::user()?->getAuthIdentifier(),
            ]);

            $fields = $type->fields()->pluck('id', 'slug')->toArray();
            $relationships = $type->relationships()->pluck('id', 'slug')->toArray();
            $reverseRelationships = $type->reverse_relationships()->pluck('id', 'reverse_slug')->toArray();
            $self->treatValidatedInstanceInput($validated, $fields, $relationships, $reverseRelationships, $instance);

            return $instance;
        });
    }

    public function updateInstance(array $validated, Type $type, Instance $instance): Instance
    {
        $self = $this;
        return DB::transaction(static function () use ($validated, $type, $instance, $self) {
            $fields = $type->fields()->pluck('id', 'slug')->toArray();
            $relationships = $type->relationships()->pluck('id', 'slug')->toArray();
            $reverseRelationships = $type->reverse_relationships()->pluck('id', 'reverse_slug')->toArray();
            $self->treatValidatedInstanceInput($validated, $fields, $relationships, $reverseRelationships, $instance);

            $instance->refresh();
            return $instance;
        });
    }
}
