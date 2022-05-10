<?php

namespace App\Http\Resources;

use App\Models\Okapi\Instance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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

        foreach ($this->type->fields as $field) {
            $result[$field->slug] = $this->values()->where('okapi_field_id', $field->id)->first()->value;
        }

        return $result;
    }
}
