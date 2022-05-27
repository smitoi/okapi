<?php

namespace App\Repositories;

use App\Models\Okapi\Field;
use App\Models\Okapi\Instance;
use App\Models\Okapi\Relationship;
use App\Models\Okapi\Type;
use App\Services\TypeService;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class InstanceRepository
{
    protected function treatValidatedInstanceInput(Type $type, array $validated): array
    {
        $validated = array_filter($validated, static fn($item) => !empty($item));

        /** @var Field $field */
        foreach ($type->fields()->get() as $field) {
            if (isset($validated[$field->slug])) {
                if ($field->type === 'password') {
                    $validated[$field->slug] = Hash::make($validated[$field->slug]);
                } elseif ($field->type === 'file') {
                    $validated[$field->slug] = $validated[$field->slug]->store('/', 'public');
                } elseif ($field->type === 'date') {
                    $validated[$field->slug] = Carbon::parse($validated[$field->slug]);
                }
            }
        }

        return $validated;
    }

    protected function detachRelationships(array $validated, Type $type, Instance $instance): void
    {
        /** @var Relationship $relationship */
        /** @var Instance $relatedInstance */
        foreach ($type->relationships()->get() as $relationship) {
            /** @var Type $related */
            $related = $relationship->toType()->firstOrFail();
            if (isset($validated[$related->slug])) {
                if ($relationship->type === 'has one' || $relationship->type === 'has many') {
                    $column = TypeService::getForeignKeyNameForType($type);
                    Instance::queryForType($related)->where($column, $instance->id)->update([
                        $column => null
                    ]);
                } elseif ($relationship->type === 'belongs to one') {
                    $column = TypeService::getForeignKeyNameForType($related);
                    $instance->{$column} = null;
                    $instance->save();
                } elseif ($relationship->type === 'belongs to many') {
                    $table = TypeService::getManyToManyTableNameForTypes($type, $related);
                    $column = TypeService::getForeignKeyNameForType($type);
                    DB::table($table)->where($column, $instance->id)->delete();
                }
            }
        }
    }

    protected function attachRelationships(array $validated, Type $type, Instance $instance): void
    {
        /** @var Relationship $relationship */
        /** @var Instance $relatedInstance */
        foreach ($type->relationships()->get() as $relationship) {
            /** @var Type $related */
            $related = $relationship->toType()->firstOrFail();
            if (isset($validated[$related->slug])) {
                $value = $validated[$related->slug];
                if ($relationship->type === 'has one') {
                    $column = TypeService::getForeignKeyNameForType($type);
                    Instance::queryForType($related)->where('id', $value)->update([
                        $column => $instance->id
                    ]);
                } elseif ($relationship->type === 'has many') {
                    $column = TypeService::getForeignKeyNameForType($type);
                    Instance::queryForType($related)->whereIn('id', $value)->update([
                        $column => $instance->id
                    ]);
                } elseif ($relationship->type === 'belongs to one') {
                    $column = TypeService::getForeignKeyNameForType($related);
                    $relatedInstance = Instance::queryForType($related)->where('id', $value)->firstOrFail();
                    $instance->{$column} = $relatedInstance->id;
                    $instance->save();
                } elseif ($relationship->type === 'belongs to many') {
                    $table = TypeService::getManyToManyTableNameForTypes($type, $related);
                    $column = TypeService::getForeignKeyNameForType($type);
                    $relatedColumn = TypeService::getForeignKeyNameForType($related);
                    DB::table($table)->insert(collect($value)->map(fn($id) => [
                        $column => $instance->id,
                        $relatedColumn => $id
                    ])->toArray());
                }
            }
        }
    }

    public function getRelationshipValuesForInstnace(Type $type, Instance $instance): void
    {
        /** @var Relationship $relationship */
        /** @var Instance $relatedInstance */
        foreach ($type->relationships()->get() as $relationship) {
            /** @var Type $related */
            $related = $relationship->toType()->firstOrFail();
            if ($relationship->type === 'has one') {
                $column = TypeService::getForeignKeyNameForType($type);
                $relatedInstance = Instance::queryForType($related)->where($column, $instance->id)->firstOrFail();
                $instance->setAttribute(
                    $related->slug,
                    $relatedInstance->id,
                );
            } elseif ($relationship->type === 'has many') {
                $column = TypeService::getForeignKeyNameForType($type);
                $instance->setAttribute(
                    $related->slug,
                    Instance::queryForType($related)->where($column, $instance->id)->get()->map(fn($item) => $item->id)->toArray()
                );
            } elseif ($relationship->type === 'belongs to one') {
                $column = TypeService::getForeignKeyNameForType($related);
                $instance->setAttribute(
                    $related->slug,
                    $instance->{$column}
                );
            } elseif ($relationship->type === 'belongs to many') {
                $table = TypeService::getManyToManyTableNameForTypes($type, $related);
                $column = TypeService::getForeignKeyNameForType($type);
                $relatedColumn = TypeService::getForeignKeyNameForType($related);
                $instance->setAttribute(
                    $related->slug,
                    DB::table($table)
                        ->where($column, $instance->id)
                        ->select($relatedColumn)
                        ->get()
                        ->map(fn($item) => $item->{$relatedColumn})->toArray()
                );
            }
        }
    }

    public function createInstance(array $validated, Type $type): Instance
    {
        $self = $this;
        return DB::transaction(function () use ($validated, $type, $self) {
            $relationshipsSlugs = $type->relationships()->get()->map(
                fn($relationship) => $relationship->toType()->firstOrFail()->slug
            )->toArray();

            $validated = $self->treatValidatedInstanceInput($type, $validated);

            $instance = new Instance;
            $instance->setTable(TypeService::getTableNameForType($type));
            foreach (Arr::except($validated, $relationshipsSlugs) as $key => $value) {
                $instance->{$key} = $value;
            }
            $instance->save();

            $this->attachRelationships($validated, $type, $instance);

            return $instance;
        });
    }

    public function updateInstance(array $validated, Type $type, Instance $instance): Instance
    {
        $self = $this;
        return DB::transaction(function () use ($validated, $type, $instance, $self) {
            $relationshipsSlugs = $type->relationships()->get()->map(
                fn($relationship) => $relationship->toType()->firstOrFail()->slug
            )->toArray();

            $validated = $self->treatValidatedInstanceInput($type, $validated);
            $instance->setTable(TypeService::getTableNameForType($type));
            foreach (Arr::except($validated, $relationshipsSlugs) as $key => $value) {
                $instance->{$key} = $value;
            }
            $instance->save();

            $this->detachRelationships($validated, $type, $instance);
            $this->attachRelationships($validated, $type, $instance);

            return $instance;
        });
    }
}
