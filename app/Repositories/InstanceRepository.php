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

        return DB::transaction(static function () use ($type, $validated, $fields) {
            /** @var Instance $instance */
            $instance = Instance::query()->create([
                'okapi_type_id' => $type->id,
            ]);

            foreach ($validated as $key => $value) {
                $instance->fields()->attach($fields[$key], [
                    'value' => $value,
                ]);
            }

            return $instance;
        });
    }

    public function updateInstance(array $validated, Type $type, Instance $instance): Instance
    {
        $fields = $type->fields()->pluck('id', 'slug')->toArray();

        return DB::transaction(static function () use ($instance, $validated, $fields) {
            $instance->fields()->detach();
            foreach ($validated as $key => $value) {
                $instance->fields()->attach($fields[$key], [
                    'value' => $value,
                ]);
            }

            $instance->refresh();
            return $instance;
        });
    }
}
