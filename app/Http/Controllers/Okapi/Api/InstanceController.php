<?php

namespace App\Http\Controllers\Okapi\Api;

use Illuminate\Http\Request;
use App\Http\Requests\Okapi\Instance\StoreInstanceRequest;
use App\Http\Requests\Okapi\Instance\UpdateInstanceRequest;
use App\Http\Resources\InstanceResource;
use App\Models\Okapi\Instance;
use App\Models\Okapi\Type;
use App\Repositories\InstanceRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class InstanceController extends ApiController
{
    private InstanceRepository $instanceRepository;

    public function __construct(InstanceRepository $instanceRepository)
    {
        $this->instanceRepository = $instanceRepository;
    }

    private function checkInstanceForPermission(Type $type, Instance $instance): JsonResponse|null
    {
        if ($instance->user_id !== Auth::user()?->getAuthIdentifier()) {
            if ($type->private) {
                return $this->jsonError(message: 'Not found', code: 404);
            }

            if ($type->ownable) {
                return $this->jsonError(message: 'Not found', code: 404);
            }
        }

        return null;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Type $type
     * @return AnonymousResourceCollection|InstanceResource|JsonResponse
     * @throws AuthorizationException
     */
    public function index(Type $type): AnonymousResourceCollection|InstanceResource|JsonResponse
    {
        $this->authorize('viewAny', [Instance::class, $type]);

        $instancesQuery = Instance::with('values')
            ->where('okapi_type_id', $type->id);

        if ($type->private) {
            $instancesQuery = $instancesQuery->where('user_id', Auth::user()?->getAuthIdentifier());
        }

        if ($type->is_collection) {
            return InstanceResource::collection(
                $instancesQuery->get(),
            );
        }

        /** @var Instance $instance */
        $instance = $instancesQuery->first();
        if ($response = $this->checkInstanceForPermission($type, $instance)) {
            return $response;
        }

        return InstanceResource::make(
            $instance,
        );

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreInstanceRequest $request
     * @param Type $type
     * @return InstanceResource|JsonResponse
     * @throws AuthorizationException
     */
    public function store(StoreInstanceRequest $request, Type $type): InstanceResource|JsonResponse
    {
        $this->authorize('create', [Instance::class, $type]);

        $instance = Instance::query()->where('okapi_type_id', $type->id)->first();
        if ($type->is_collection || empty($instance)) {
            $validated = $request->all();
            $instance = $this->instanceRepository->createInstance($validated, $type);
            return InstanceResource::make($instance);
        }

        return $this->jsonError(message: 'Bad request', code: 400);
    }

    /**
     * Display the specified resource.
     *
     * @param Type $type
     * @param Instance $instance
     * @return InstanceResource
     * @throws AuthorizationException
     */
    public function show(Type $type, Instance $instance): InstanceResource
    {
        $this->authorize('view', $instance);
        return InstanceResource::make($instance);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateInstanceRequest $request
     * @param Type $type
     * @param Instance $instance
     * @return InstanceResource|JsonResponse
     * @throws AuthorizationException
     */
    public function update(UpdateInstanceRequest $request, Type $type, Instance $instance): InstanceResource|JsonResponse
    {
        $this->authorize('update', $instance);
        if ($response = $this->checkInstanceForPermission($type, $instance)) {
            return $response;
        }
        $validated = $request->all();
        $instance = $this->instanceRepository->updateInstance($validated, $type, $instance);
        return InstanceResource::make($instance);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Type $type
     * @param Instance $instance
     * @return Response|JsonResponse
     * @throws AuthorizationException
     */
    public function destroy(Type $type, Instance $instance): Response|JsonResponse
    {
        $this->authorize('delete', $instance);
        if ($response = $this->checkInstanceForPermission($type, $instance)) {
            return $response;
        }
        $instance->delete();
        return response()->noContent();
    }
}
