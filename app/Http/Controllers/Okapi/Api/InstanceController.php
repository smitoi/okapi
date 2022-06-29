<?php

namespace App\Http\Controllers\Okapi\Api;

use App\Models\Okapi\ApiKey;
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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InstanceController extends ApiController
{
    private InstanceRepository $instanceRepository;

    public function __construct(InstanceRepository $instanceRepository)
    {
        $this->instanceRepository = $instanceRepository;
    }

    private function apiKeyHasPermission(ApiKey $apiKey, string $method, Type $type): bool
    {
        if ($apiKey->permissions()
            ->where([
                'target_id' => $type->id,
                'target_type' => Type::class
            ])
            ->where('name', 'like', "%$method%")->doesntExist()) {
            return false;
        }

        return true;
    }


    private function checkInstanceForPermission(Type $type, Instance|null $instance): JsonResponse|null
    {
        if ($instance === null) {
            throw new NotFoundHttpException();
        }

        if ($instance->created_by !== Auth::user()?->getAuthIdentifier()) {
            if ($type->private) {
                return $this->jsonError(message: 'Not found', code: 404);
            }

            if ($type->ownable) {
                return $this->jsonError(message: 'Not authorized', code: 403);
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
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function store(StoreInstanceRequest $request, Type $type): JsonResponse
    {
        $this->authorize('create', [Instance::class, $type]);

        $instance = Instance::queryForType($type)->first();
        if ($type->is_collection || empty($instance)) {
            $validated = $request->validated();
            $instance = $this->instanceRepository->createInstance($validated, $type);
            return $this->jsonSuccess(
                data: $this->instanceRepository->transformInstanceToJson($instance, $type));
        }

        return $this->jsonError(message: 'Bad request', code: 400);
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param Type $type
     * @param string $instance
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function show(Request $request, Type $type, string $instance): JsonResponse
    {
        if ($request->apiKey && $this->apiKeyHasPermission($request->apiKey, 'delete', $type)) {
            return $this->jsonError(message: 'Not authorized', code: 403);
        }

        $this->authorize('view', [Instance::class, $type]);

        /** @var Instance $instanceModel */
        $instanceModel = Instance::queryForType($type)->where('id', $instance)->firstOrFail();
        return $this->jsonSuccess(
            data: $this->instanceRepository->transformInstanceToJson($instanceModel, $type));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateInstanceRequest $request
     * @param Type $type
     * @param string $instance
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function update(UpdateInstanceRequest $request, Type $type, string $instance): JsonResponse
    {
        if ($request->apiKey && $this->apiKeyHasPermission($request->apiKey, 'delete', $type)) {
            return $this->jsonError(message: 'Not authorized', code: 403);
        }

        $this->authorize('update', [Instance::class, $type]);

        /** @var Instance $instanceModel */
        $instanceModel = Instance::queryForType($type)->where('id', $instance)->firstOrFail();
        if ($response = $this->checkInstanceForPermission($type, $instanceModel)) {
            return $response;
        }
        $validated = $request->validated();
        $instanceModel = $this->instanceRepository->updateInstance($validated, $type, $instanceModel);
        return $this->jsonSuccess(
            data: $this->instanceRepository->transformInstanceToJson($instanceModel, $type));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param Type $type
     * @param string $instance
     * @return Response|JsonResponse
     * @throws AuthorizationException
     */
    public function destroy(Request $request, Type $type, string $instance): Response|JsonResponse
    {
        if ($request->apiKey && $this->apiKeyHasPermission($request->apiKey, 'delete', $type)) {
            return $this->jsonError(message: 'Not authorized', code: 403);
        }

        $this->authorize('delete', [Instance::class, $type]);

        /** @var Instance $instanceModel */
        $instanceModel = Instance::queryForType($type)->where('id', $instance)->firstOrFail();

        if ($response = $this->checkInstanceForPermission($type, $instanceModel)) {
            return $response;
        }

        Instance::queryForType($type)->where('id', $instance)->delete();
        return response()->noContent();
    }
}
