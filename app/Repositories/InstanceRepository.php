<?php

namespace App\Repositories;

use App\Models\Okapi\Instance;
use App\Models\Okapi\Type;
use Illuminate\Support\Facades\DB;

class InstanceRepository
{
    private function treatValidatedInstanceInput($validated, $fields, $relationships, $instance)
    {
        foreach ($validated as $key => $value) {
            if (array_key_exists($key, $fields)) {
                $instance->fields()->attach($fields[$key], [
                    'value' => $value,
                ]);
            } else {
                if (!is_array($value)) {
                    $value = [$value];
                }

                foreach ($value as $related) {
                    $instance->relationships()->attach($relationships[$key], [
                        'okapi_to_instance_id' => $related,
                    ]);
                }
            }
        }
    }

    public function createInstance(array $validated, Type $type): Instance
    {
        $self = $this;
        return DB::transaction(static function () use ($validated, $type, $self) {
            /** @var Instance $instance */
            $instance = Instance::query()->create([
                'okapi_type_id' => $type->id,
            ]);

            $fields = $type->fields()->pluck('id', 'slug')->toArray();
            $relationships = $type->relationships()->pluck('id', 'slug')->toArray();
            $self->treatValidatedInstanceInput($validated, $fields, $relationships, $instance);

            return $instance;
        });
    }

    public function updateInstance(array $validated, Type $type, Instance $instance): Instance
    {
        $self = $this;
        return DB::transaction(static function () use ($validated, $type, $instance, $self) {
            $instance->fields()->detach();
            $instance->relationships()->detach();

            $fields = $type->fields()->pluck('id', 'slug')->toArray();
            $relationships = $type->relationships()->pluck('id', 'slug')->toArray();
            $self->treatValidatedInstanceInput($validated, $fields, $relationships, $instance);

            $instance->refresh();
            return $instance;
        });
    }
}
