<?php

namespace App\Http\Controllers\Okapi\Api;

use App\Models\Okapi\Field;
use Illuminate\Http\Request;
use App\Http\Requests\Okapi\Instance\StoreInstanceRequest;
use App\Http\Requests\Okapi\Instance\UpdateInstanceRequest;
use App\Models\Okapi\Instance;
use App\Models\Okapi\Type;
use App\Repositories\InstanceRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
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
        if ($instance->created_by !== Auth::user()?->getAuthIdentifier()) {
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
     * @param Request $request
     * @param Type $type
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function index(Request $request, Type $type): JsonResponse
    {
        $this->authorize('viewAny', [Instance::class, $type]);

        $instancesQuery = Instance::queryForType($type);

        if ($type->private) {
            $instancesQuery = $instancesQuery->where('user_id', Auth::user()?->getAuthIdentifier());
        }

        if ($request->query('filter')) {
            $filter = $request->query('filter');

            foreach ($filter as $key => $value) {
                $field = Field::query()->where('slug', $key)->first();

                if ($field) {
                    $instancesQuery = $instancesQuery->where($key, $value);
                }
            }
        }

        if ($type->is_collection) {
            return $this->jsonSuccess(
                data: $this->instanceRepository->transformInstancesToJson($instancesQuery->get(), $type));
        }

        /** @var Instance $instance */
        $instance = $instancesQuery->first();
        if ($response = $this->checkInstanceForPermission($type, $instance)) {
            return $response;
        }

        return $this->jsonSuccess(
            data: $this->instanceRepository->transformInstanceToJson($instance, $type),
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

        $instance = Instance::queryForType($type)->first();
        if ($type->is_collection || empty($instance)) {
            $validated = $request->validated();
            $instance = $this->instanceRepository->createInstance($validated, $type);
            return InstanceResource::make($instance, $type);
        }

        return $this->jsonError(message: 'Bad request', code: 400);
    }

    /**
     * Display the specified resource.
     *
     * @param Type $type
     * @param string $instance
     * @return InstanceResource
     * @throws AuthorizationException
     */
    public function show(Type $type, string $instance): InstanceResource
    {
        $this->authorize('view', $instance);
        /** @var Instance $instanceModel */
        $instanceModel = Instance::queryForType($type)->where('id', $instance)->firstOrFail();
        return InstanceResource::make($instanceModel, $type);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateInstanceRequest $request
     * @param Type $type
     * @param string $instance
     * @return InstanceResource|JsonResponse
     * @throws AuthorizationException
     */
    public function update(UpdateInstanceRequest $request, Type $type, string $instance): InstanceResource|JsonResponse
    {
        $this->authorize('update', $instance);
        /** @var Instance $instanceModel */
        $instanceModel = Instance::queryForType($type)->where('id', $instance)->firstOrFail();
        if ($response = $this->checkInstanceForPermission($type, $instanceModel)) {
            return $response;
        }
        $validated = $request->validated();
        $instanceModel = $this->instanceRepository->updateInstance($validated, $type, $instanceModel);
        return InstanceResource::make($instanceModel, $type);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Type $type
     * @param string $instance
     * @return Response|JsonResponse
     * @throws AuthorizationException
     */
    public function destroy(Type $type, string $instance): Response|JsonResponse
    {
        /** @var Instance $instanceModel */
        $instanceModel = Instance::queryForType($type)->where('id', $instance)->firstOrFail();

        $this->authorize('delete', $instanceModel);
        if ($response = $this->checkInstanceForPermission($type, $instanceModel)) {
            return $response;
        }

        Instance::queryForType($type)->where('id', $instance)->delete();
        return response()->noContent();
    }
}
