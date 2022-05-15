<?php

namespace App\Http\Controllers\Okapi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Okapi\Instance\StoreInstanceRequest;
use App\Http\Requests\Okapi\Instance\UpdateInstanceRequest;
use App\Models\Okapi\Instance;
use App\Models\Okapi\InstanceField;
use App\Models\Okapi\Relationship;
use App\Models\Okapi\Type;
use App\Repositories\InstanceRepository;
use App\Repositories\TypeRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
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

    /**
     * Display a listing of the resource.
     *
     * @param Type $type
     * @return Response|RedirectResponse
     */
    public function index(Type $type): Response|RedirectResponse
    {
        if ($type->is_collection) {
            return Inertia::render('Okapi/Instance/List', [
                'type' => $type->load('fields'),
                'instances' => Instance::with('values')
                    ->where('okapi_type_id', $type->id)
                    ->get(),
            ]);
        }

        $instance = Instance::query()->where('okapi_type_id', $type->id)->first();
        if ($instance) {
            return redirect()->route('okapi-instances.edit', ['type' => $type, 'instance' => $instance]);
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
        $instance = Instance::where('okapi_type_id', $type->id)->first();
        if ($type->is_collection || empty($instance)) {
            $type->load('fields');
            $relationships = $this->typeRepository->getRelationshipsWithOptions($type);
            return Inertia::render('Okapi/Instance/New', [
                'type' => $type,
                'relationships' => $relationships,
                'relationshipReverses' => Relationship::REVERSE_RELATIONSHIPS,
            ]);
        }

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
        $instance = Instance::where('okapi_type_id', $type->id)->first();
        if ($type->is_collection || empty($instance)) {
            $validated = $request->all();
            $this->instanceRepository->createInstance($validated, $type);
            return redirect()->route('okapi-instances.index', $type);
        }

        abort(400);
    }

    /**
     * Display the specified resource.
     *
     * @param Type $type
     * @param Instance $instance
     * @return Response
     */
    public function show(Type $type, Instance $instance): Response
    {
        $type->load('fields', 'relationships', 'reverse_relationships');
        $relationships = $this->typeRepository->getRelationshipsWithOptions($type);
        $instance->load('values', 'relationships', 'reverse_relationships', 'reverse_related', 'related');

        return Inertia::render('Okapi/Instance/Show', [
            'type' => $type,
            'instance' => $instance,
            'relationships' => $relationships,
            'relationshipReverses' => Relationship::REVERSE_RELATIONSHIPS,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Type $type
     * @param Instance $instance
     * @return Response
     */
    public function edit(Type $type, Instance $instance): Response
    {
        $type->load('fields', 'relationships', 'reverse_relationships');
        $relationships = $this->typeRepository->getRelationshipsWithOptions($type);
        $instance->load('values', 'relationships', 'reverse_relationships', 'reverse_related', 'related');

        return Inertia::render('Okapi/Instance/Edit', [
            'type' => $type,
            'instance' => $instance,
            'relationships' => $relationships,
            'relationshipReverses' => Relationship::REVERSE_RELATIONSHIPS,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateInstanceRequest $request
     * @param Type $type
     * @param Instance $instance
     * @return RedirectResponse
     */
    public function update(UpdateInstanceRequest $request, Type $type, Instance $instance): RedirectResponse
    {
        $validated = $request->all();
        $this->instanceRepository->updateInstance($validated, $type, $instance);
        return redirect()->route('okapi-instances.index', $type);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Type $type
     * @param Instance $instance
     * @return RedirectResponse
     */
    public function destroy(Type $type, Instance $instance): RedirectResponse
    {
        $instance->delete();
        return redirect()->route('okapi-instances.index', $type);
    }
}
