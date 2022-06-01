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
        foreach ($type->relationships as $relationship) {
            $key = TypeService::getForeignKeyNameForRelationship($relationship);
            /** @var Type $related */
            $related = $relationship->toType;
            if (Arr::exists($validated, $key)) {
                if ($relationship->type === 'has one' || $relationship->type === 'has many') {
                    $column = TypeService::getForeignKeyNameForRelationship($relationship);
                    Instance::queryForType($related)->where($column, $instance->id)->update([
                        $column => null
                    ]);
                } elseif ($relationship->type === 'belongs to one') {
                    $column = TypeService::getReverseForeignKeyNameForRelationship($relationship);
                    $instance->{$column} = null;
                    $instance->save();
                } elseif ($relationship->type === 'belongs to many') {
                    $table = TypeService::getManyToManyTableNameForRelationship($relationship);
                    $column = TypeService::getForeignKeyNameForRelationship($relationship);
                    DB::table($table)->where($column, $instance->id)->delete();
                }
            }
        }
    }

    protected function detachReverseRelationships(array $validated, Type $type, Instance $instance): void
    {
        /** @var Relationship $relationship */
        /** @var Instance $relatedInstance */
        foreach ($type->reverseRelationships as $relationship) {
            $key = TypeService::getReverseForeignKeyNameForRelationship($relationship);
            /** @var Type $related */
            $related = $relationship->fromType;
            if (Arr::exists($validated, $key)) {
                if ($relationship->type === 'has many' || $relationship->type === 'has one') {
                    $column = TypeService::getForeignKeyNameForRelationship($relationship);
                    $instance->{$column} = null;
                } elseif ($relationship->type === 'belongs to one') {
                    $column = TypeService::getForeignKeyNameForRelationship($relationship);
                    Instance::queryForType($related)->whereIn('id', $instance->id)->update([
                        $column => null,
                    ]);
                } elseif ($relationship->type === 'belongs to many') {
                    $table = TypeService::getManyToManyTableNameForRelationship($relationship);
                    $column = TypeService::getReverseForeignKeyNameForRelationship($relationship);
                    DB::table($table)->where($column, $instance->id)->delete();
                }
            }
        }
    }

    protected function attachRelationships(array $validated, Type $type, Instance $instance): void
    {
        /** @var Relationship $relationship */
        /** @var Instance $relatedInstance */
        foreach ($type->relationships as $relationship) {
            $key = TypeService::getForeignKeyNameForRelationship($relationship);
            $related = $relationship->toType;
            if (Arr::exists($validated, $key)) {
                $value = $validated[$key];
                if ($relationship->type === 'has one') {
                    $column = TypeService::getForeignKeyNameForRelationship($relationship);
                    Instance::queryForType($related)->where('id', $value)->update([
                        $column => $instance->id
                    ]);
                } elseif ($relationship->type === 'has many') {
                    $column = TypeService::getForeignKeyNameForRelationship($relationship);
                    Instance::queryForType($related)->whereIn('id', $value)->update([
                        $column => $instance->id
                    ]);
                } elseif ($relationship->type === 'belongs to one') {
                    $column = TypeService::getReverseForeignKeyNameForRelationship($relationship);
                    $relatedInstance = Instance::queryForType($related)->where('id', $value)->firstOrFail();
                    $instance->{$column} = $relatedInstance->id;
                    $instance->save();
                } elseif ($relationship->type === 'belongs to many') {
                    $table = TypeService::getManyToManyTableNameForRelationship($relationship);
                    $column = TypeService::getForeignKeyNameForRelationship($relationship);
                    $relatedColumn = TypeService::getReverseForeignKeyNameForRelationship($relationship);
                    DB::table($table)->insert(collect($value)->map(fn($id) => [
                        $column => $instance->id,
                        $relatedColumn => $id
                    ])->toArray());
                }
            }
        }
    }

    protected function attachReverseRelationships(array $validated, Type $type, Instance $instance): void
    {
        /** @var Relationship $relationship */
        /** @var Instance $relatedInstance */
        foreach ($type->reverseRelationships as $relationship) {
            $key = TypeService::getReverseForeignKeyNameForRelationship($relationship);
            $related = $relationship->fromType;
            if (Arr::exists($validated, $key)) {
                $value = $validated[$key];
                if ($relationship->type === 'has many' || $relationship->type === 'has one') {
                    $column = TypeService::getForeignKeyNameForRelationship($relationship);
                    $relatedInstance = Instance::queryForType($related)->where('id', $value)->first();
                    $instance->{$column} = $relatedInstance?->id;
                } elseif ($relationship->type === 'belongs to one') {
                    $column = TypeService::getForeignKeyNameForRelationship($relationship);
                    Instance::queryForType($related)->whereIn('id', $value)->update([
                        $column => $instance->id
                    ]);
                } elseif ($relationship->type === 'belongs to many') {
                    $table = TypeService::getManyToManyTableNameForRelationship($relationship);
                    $column = TypeService::getReverseForeignKeyNameForRelationship($relationship);
                    $relatedColumn = TypeService::getForeignKeyNameForRelationship($relationship);
                    DB::table($table)->insert(collect($value)->map(fn($id) => [
                        $column => $instance->id,
                        $relatedColumn => $id
                    ])->toArray());
                }
            }
        }
    }

    public function getRelationshipValuesForInstance(Type $type, Instance $instance): void
    {
        /** @var Relationship $relationship */
        /** @var Instance $relatedInstance */
        foreach ($type->relationships as $relationship) {
            /** @var Type $related */
            $related = $relationship->toType;
            if ($relationship->type === 'has one') {
                $column = TypeService::getForeignKeyNameForRelationship($relationship);
                $relatedInstance = Instance::queryForType($related)->where($column, $instance->id)->first();
                $instance->setAttribute(
                    $column,
                    $relatedInstance?->id,
                );
            } elseif ($relationship->type === 'has many') {
                $column = TypeService::getForeignKeyNameForRelationship($relationship);
                $instance->setAttribute(
                    $column,
                    Instance::queryForType($related)->where($column, $instance->id)->get()->map(fn($item) => $item->id)->toArray()
                );
            } elseif ($relationship->type === 'belongs to one') {
                $column = TypeService::getReverseForeignKeyNameForRelationship($relationship);
                $instance->setAttribute(
                    $column,
                    $instance->{$column}
                );
            } elseif ($relationship->type === 'belongs to many') {
                $table = TypeService::getManyToManyTableNameForRelationship($relationship);
                $column = TypeService::getForeignKeyNameForRelationship($relationship);
                $relatedColumn = TypeService::getReverseForeignKeyNameForRelationship($relationship);
                $instance->setAttribute(
                    $column,
                    DB::table($table)
                        ->where($column, $instance->id)
                        ->select($relatedColumn)
                        ->get()
                        ->map(fn($item) => $item->{$relatedColumn})->toArray()
                );
            }
        }
    }

    public function getReverseRelationshipValuesForInstance(Type $type, Instance $instance): void
    {
        /** @var Relationship $relationship */
        /** @var Instance $relatedInstance */
        foreach ($type->reverseRelationships as $relationship) {
            /** @var Type $related */
            $related = $relationship->fromType;
            if ($relationship->type === 'has one') {
                $column = TypeService::getForeignKeyNameForRelationship($relationship);
                $relatedInstance = Instance::queryForType($related)->where('id', $instance->{$column})->first();
                $instance->setAttribute(
                    TypeService::getReverseForeignKeyNameForRelationship($relationship),
                    $relatedInstance?->id,
                );
            } elseif ($relationship->type === 'has many') {
                $column = TypeService::getForeignKeyNameForRelationship($relationship);
                $relatedInstance = Instance::queryForType($related)->where('id', $instance->{$column})->first();
                $instance->setAttribute(
                    TypeService::getReverseForeignKeyNameForRelationship($relationship),
                    $relatedInstance?->id
                );
            } elseif ($relationship->type === 'belongs to one') {
                $column = TypeService::getForeignKeyNameForRelationship($relationship);
                $instance->setAttribute(
                    TypeService::getReverseForeignKeyNameForRelationship($relationship),
                    Instance::queryForType($related)->where('id', $instance->{$column})->get()->map(fn($item) => $item->id)->toArray()
                );
            } elseif ($relationship->type === 'belongs to many') {
                $table = TypeService::getManyToManyTableNameForRelationship($relationship);
                $column = TypeService::getForeignKeyNameForRelationship($relationship);
                $relatedColumn = TypeService::getReverseForeignKeyNameForRelationship($relationship);
                $instance->setAttribute(
                    $relatedColumn,
                    DB::table($table)
                        ->where($relatedColumn, $instance->id)
                        ->select($column)
                        ->get()
                        ->map(fn($item) => $item->{$column})->toArray()
                );
            }
        }
    }

    public function createInstance(array $validated, Type $type): Instance
    {
        $self = $this;
        return DB::transaction(function () use ($validated, $type, $self) {
            $fieldSlugs = $type->fields()->get()->map(fn(Field $field) => $field->slug)->toArray();

            $validated = $self->treatValidatedInstanceInput($type, $validated);

            $instance = new Instance;
            $instance->setTable(TypeService::getTableNameForType($type));
            foreach (Arr::only($validated, $fieldSlugs) as $key => $value) {
                $instance->{$key} = $value;
            }
            $instance->save();

            $this->attachRelationships($validated, $type, $instance);
            $this->attachReverseRelationships($validated, $type, $instance);

            $instance->save();
            return $instance;
        });
    }

    public function updateInstance(array $validated, Type $type, Instance $instance): Instance
    {
        $self = $this;
        return DB::transaction(function () use ($validated, $type, $instance, $self) {
            $fieldSlugs = $type->fields()->get()->map(fn(Field $field) => $field->slug)->toArray();

            $validated = $self->treatValidatedInstanceInput($type, $validated);
            $instance->setTable(TypeService::getTableNameForType($type));
            foreach (Arr::only($validated, $fieldSlugs) as $key => $value) {
                $instance->{$key} = $value;
            }
            $instance->save();

            $this->detachRelationships($validated, $type, $instance);
            $this->detachReverseRelationships($validated, $type, $instance);
            $this->attachRelationships($validated, $type, $instance);
            $this->attachReverseRelationships($validated, $type, $instance);

            $instance->save();
            return $instance;
        });
    }
}
