<?php

namespace App\Http\Controllers\Okapi\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Okapi\StoreInstanceRequest;
use App\Http\Requests\Okapi\UpdateInstanceRequest;
use App\Http\Resources\InstanceResource;
use App\Models\Okapi\Instance;
use App\Models\Okapi\Type;
use App\Repositories\InstanceRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class InstanceController extends Controller
{
    private InstanceRepository $instanceRepository;

    public function __construct(InstanceRepository $instanceRepository)
    {
        $this->instanceRepository = $instanceRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Type $type
     * @return AnonymousResourceCollection
     */
    public function index(Type $type): AnonymousResourceCollection
    {
        return InstanceResource::collection(
            Instance::with('values')
                ->where('okapi_type_id', $type->id)
                ->get(),
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreInstanceRequest $request
     * @param Type $type
     * @return InstanceResource
     */
    public function store(StoreInstanceRequest $request, Type $type): InstanceResource
    {
        $validated = $request->all();
        $instance = $this->instanceRepository->createInstance($validated, $type);
        return InstanceResource::make($instance);
    }

    /**
     * Display the specified resource.
     *
     * @param Instance $instance
     * @return void
     */
    public function show(Instance $instance): void
    {
        // TODO: Decide if needed and implement
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateInstanceRequest $request
     * @param Type $type
     * @param Instance $instance
     * @return InstanceResource
     */
    public function update(UpdateInstanceRequest $request, Type $type, Instance $instance): InstanceResource
    {
        $validated = $request->all();
        $instance = $this->instanceRepository->updateInstance($validated, $type, $instance);
        return InstanceResource::make($instance);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Type $type
     * @param Instance $instance
     * @return Response
     */
    public function destroy(Type $type, Instance $instance): Response
    {
        $instance->delete();
        return response()->noContent();
    }
}
