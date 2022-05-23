<?php

namespace App\Http\Resources;

use App\Models\Okapi\Field;
use App\Models\Okapi\Instance;
use App\Models\Okapi\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class InstanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Instance $this */
        $result = [
            'id' => $this->id,
        ];

        /** @var Type $type */
        $type = $this->type;

        foreach ($type->fields as $field) {
            $value = $this->values()->where('okapi_field_id', $field->id)->first()?->value;

            if ($field->type === Field::TYPE_FILE) {
                $result[$field->slug] = Storage::disk('public')->url($value);
            } else {
                $result[$field->slug] = $value;
            }
        }

        foreach ($type->relationships as $relationship) {
            $result[$relationship->slug] = $this->related()
                ->where('okapi_relationship_id', $relationship->id)
                ->get()->map(fn ($item) => $item->id);
        }

        foreach ($type->reverse_relationships as $relationship) {
            $result[$relationship->reverse_slug] = $this->reverse_related()
                ->where('okapi_relationship_id', $relationship->id)
                ->get()->map(fn ($item) => $item->id);
        }

        return $result;
    }
}
