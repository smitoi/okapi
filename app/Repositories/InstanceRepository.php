<?php

namespace App\Repositories;

use App\Models\Okapi\Instance;
use App\Models\Okapi\Type;
use Illuminate\Support\Facades\DB;

class InstanceRepository
{
    public function createInstance(array $validated, Type $type): Instance
    {
        $fields = $type->fields()->pluck('id', 'slug')->toArray();
        $relationships = $type->relationships()->pluck('id', 'slug')->toArray();

        return DB::transaction(static function () use ($type, $validated, $fields, $relationships) {
            /** @var Instance $instance */
            $instance = Instance::query()->create([
                'okapi_type_id' => $type->id,
            ]);

            foreach ($validated as $key => $value) {
                if (array_key_exists($key, $fields)) {
                    $instance->fields()->attach($fields[$key], [
                        'value' => $value,
                    ]);
                } else {
                    $instance->relationships()->attach($relationships[$key], [
                        'okapi_to_instance_id' => $value,
                    ]);
                }
            }

            return $instance;
        });
    }

    public function updateInstance(array $validated, Type $type, Instance $instance): Instance
    {
        $fields = $type->fields()->pluck('id', 'slug')->toArray();
        $relationships = $type->relationships()->pluck('id', 'slug')->toArray();

        return DB::transaction(static function () use ($instance, $validated, $fields, $relationships) {
            $instance->fields()->detach();
            $instance->relationships()->detach();
            foreach ($validated as $key => $value) {
                if (array_key_exists($key, $fields)) {
                    $instance->fields()->attach($fields[$key], [
                        'value' => $value,
                    ]);
                } else {
                    $instance->relationships()->attach($relationships[$key], [
                        'okapi_to_instance_id' => $value,
                    ]);
                }
            }

            $instance->refresh();
            return $instance;
        });
    }
}
