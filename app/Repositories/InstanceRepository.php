<?php

namespace App\Repositories;

use App\Models\Okapi\Field;
use App\Models\Okapi\Instance;
use App\Models\Okapi\Type;
use App\Services\TypeService;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class InstanceRepository
{
    private function treatValidatedInstanceInput(Type $type, array $validated): array
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

    public function createInstance(array $validated, Type $type): Instance
    {
        $self = $this;
        return DB::transaction(static function () use ($validated, $type, $self) {
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

            return $instance;
        });
    }

    public function updateInstance(array $validated, Type $type, Instance $instance): Instance
    {
        $self = $this;
        return DB::transaction(static function () use ($validated, $type, $instance, $self) {
            $relationshipsSlugs = $type->relationships()->get()->map(
                fn($relationship) => $relationship->toType()->firstOrFail()->slug
            )->toArray();

            $validated = $self->treatValidatedInstanceInput($type, $validated);
            $instance->setTable(TypeService::getTableNameForType($type));
            foreach (Arr::except($validated, $relationshipsSlugs) as $key => $value) {
                $instance->{$key} = $value;
            }

            $instance->save();
            return $instance;
        });
    }
}
