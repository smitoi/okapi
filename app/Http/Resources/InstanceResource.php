<?php

namespace App\Http\Resources;

use App\Models\Okapi\Field;
use App\Models\Okapi\Instance;
use App\Models\Okapi\Relationship;
use App\Models\Okapi\Type;
use App\Repositories\InstanceRepository;
use App\Services\TypeService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use JetBrains\PhpStorm\Pure;

class InstanceResource extends JsonResource
{
    protected Type $okapiType;
    protected int $apiLevel;
    protected array $recursiveTypes;

    #[Pure] public function __construct(Instance $resource, Type $okapiType, int $apiLevel = 1, array $recursiveTypes = [])
    {
        parent::__construct($resource);
        $this->okapiType = $okapiType;
        $this->apiLevel = $apiLevel;
        $this->recursiveTypes = $recursiveTypes;
    }

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

        /** @var Field $field */
        foreach ($this->okapiType->fields()->get() as $field) {
            $value = $this->resource->{$field->slug};

            if ($field->type === 'field') {
                $result[$field->slug] = Storage::disk('public')->url($value);
            } else {
                $result[$field->slug] = $value;
            }
        }

        App::make(InstanceRepository::class)
            ->getRelationshipValuesForInstance($this->okapiType, $this->resource);

        /** @var Relationship $relationship */
        foreach ($this->okapiType->relationships()->with('toType')->get()
                 as $relationship) {
            /** @var Type $related */
            $related = $relationship->toType()->firstOrFail();
            $key = TypeService::getTableNameForType($related);

            $result[$key] = [];
        }

        return $result;
    }
}
