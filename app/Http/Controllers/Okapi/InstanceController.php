<?php

namespace App\Http\Controllers\Okapi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Okapi\Instance\StoreInstanceRequest;
use App\Http\Requests\Okapi\Instance\UpdateInstanceRequest;
use App\Models\Okapi\Instance;
use App\Models\Okapi\Type;
use App\Repositories\InstanceRepository;
use App\Repositories\TypeRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class InstanceController extends Controller
{
    private InstanceRepository $instanceRepository;
    private TypeRepository $typeRepository;

    public function __construct(InstanceRepository $instanceRepository, TypeRepository $typeRepository)
    {
        $this->instanceRepository = $instanceRepository;
        $this->typeRepository = $typeRepository;
    }

    private function checkInstanceForPermission(Type $type, Instance $instance): void
    {
        if ($instance->user_id !== Auth::user()?->getAuthIdentifier()) {
            if ($type->private) {
                abort(404);
            } elseif ($type->ownable) {
                abort(403);
            }
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @param Type $type
     * @return Response|RedirectResponse
     */
    public function index(Type $type): Response|RedirectResponse
    {
        $type->load('fields');
        $instancesQuery = Instance::queryForType($type);

        if ($type->private) {
            $instancesQuery = $instancesQuery->where('created_by', Auth::user()?->getAuthIdentifier());
        }

        if ($type->is_collection) {
            return Inertia::render('Okapi/Instance/List', [
                'type' => $type,
                'instances' => $instancesQuery->get(),
            ]);
        }

        $instance = $instancesQuery->first();
        if ($instance) {
            return redirect()->route('okapi-instances.show', [
                'type' => $type,
                'instance' => $instance
            ]);
        }

        return redirect()->route('okapi-instances.create', $type);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Type $type
     * @return Response|RedirectResponse
     */
    public function create(Type $type): Response|RedirectResponse
    {
        /** @var Instance $instance */
        $instance = Instance::queryForType($type)->first();

        if ($type->is_collection || $instance === null) {
            $type->load('fields');
            return Inertia::render('Okapi/Instance/New', [
                'type' => $type,
                'relationships' => $this->typeRepository->getRelationshipsWithOptions($type),
                'reverseRelationships' => $this->typeRepository->getRelationshipsWithOptions($type, true),
            ]);
        }

        $this->checkInstanceForPermission($type, $instance);
        return redirect()->route('okapi-instances.edit', ['type' => $type, 'instance' => $instance]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreInstanceRequest $request
     * @param Type $type
     * @return RedirectResponse
     */
    public function store(StoreInstanceRequest $request, Type $type): RedirectResponse
    {
        $instance = Instance::queryForType($type)->first();
        if ($type->is_collection || empty($instance)) {
            $validated = $request->validated();
            $this->instanceRepository->createInstance($validated, $type);
            return redirect()->route('okapi-instances.index', $type);
        }

        abort(400);
    }

    /**
     * Display the specified resource.
     *
     * @param Type $type
     * @param string $instance
     * @return Response
     */
    public function show(Type $type, string $instance): Response
    {
        /** @var Instance $instanceModel */
        $instanceModel = Instance::queryForType($type)->where('id', $instance)->firstOrFail();
        $type->load('fields');
        $this->instanceRepository->getRelationshipValuesForInstance($type, $instanceModel);
        $this->instanceRepository->getReverseRelationshipValuesForInstance($type, $instanceModel);

        $this->checkInstanceForPermission($type, $instanceModel);
        return Inertia::render('Okapi/Instance/Show', [
            'type' => $type,
            'instance' => $instanceModel,
            'relationships' => $this->typeRepository->getRelationshipsWithOptions($type),
            'reverseRelationships' => $this->typeRepository->getRelationshipsWithOptions($type, true),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Type $type
     * @param string $instance
     * @return Response
     */
    public function edit(Type $type, string $instance): Response
    {
        /** @var Instance $instanceModel */
        $instanceModel = Instance::queryForType($type)->where('id', $instance)->firstOrFail();
        $type->load('fields');
        $this->instanceRepository->getRelationshipValuesForInstance($type, $instanceModel);
        $this->instanceRepository->getReverseRelationshipValuesForInstance($type, $instanceModel);

        $this->checkInstanceForPermission($type, $instanceModel);
        return Inertia::render('Okapi/Instance/Edit', [
            'type' => $type,
            'instance' => $instanceModel,
            'relationships' => $this->typeRepository->getRelationshipsWithOptions($type),
            'reverseRelationships' => $this->typeRepository->getRelationshipsWithOptions($type, true),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateInstanceRequest $request
     * @param Type $type
     * @param string $instance
     * @return RedirectResponse
     */
    public function update(UpdateInstanceRequest $request, Type $type, string $instance): RedirectResponse
    {
        /** @var Instance $instanceModel */
        $instanceModel = Instance::queryForType($type)->where('id', $instance)->firstOrFail();
        $this->checkInstanceForPermission($type, $instanceModel);

        $validated = $request->validated();
        $this->instanceRepository->updateInstance($validated, $type, $instanceModel);

        return redirect()->route('okapi-instances.index', $type);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Type $type
     * @param string $instance
     * @return RedirectResponse
     */
    public function destroy(Type $type, string $instance): RedirectResponse
    {
        /** @var Instance $instanceModel */
        $instanceModel = Instance::queryForType($type)->where('id', $instance)->firstOrFail();
        $this->checkInstanceForPermission($type, $instanceModel);
        Instance::queryForType($type)->where('id', $instance)->delete();
        return redirect()->route('okapi-instances.index', $type);
    }
}
