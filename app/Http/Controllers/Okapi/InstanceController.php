<?php

namespace App\Http\Controllers\Okapi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Okapi\StoreInstanceRequest;
use App\Http\Requests\Okapi\UpdateInstanceRequest;
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
     * @return Response
     */
    public function index(Type $type): Response
    {
        return Inertia::render('Okapi/Instance/List', [
            'type' => $type->load('fields'),
            'instances' => Instance::with('values')
                ->where('okapi_type_id', $type->id)
                ->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Type $type
     * @return Response
     */
    public function create(Type $type): Response
    {
        $type->load('fields');
        $relationships = $this->typeRepository->getRelationshipsWithOptions($type);
        return Inertia::render('Okapi/Instance/New', [
            'type' => $type,
            'relationships' => $relationships,
        ]);
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
        $validated = $request->all();
        $this->instanceRepository->createInstance($validated, $type);
        return redirect()->route('okapi-instances.index', $type);
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
     * Show the form for editing the specified resource.
     *
     * @param Type $type
     * @param Instance $instance
     * @return Response
     */
    public function edit(Type $type, Instance $instance): Response
    {
        $type->load('fields', 'relationships');
        $relationships = $this->typeRepository->getRelationshipsWithOptions($type);
        $instance->load('values', 'relationships', 'related');

        return Inertia::render('Okapi/Instance/Edit', [
            'type' => $type,
            'instance' => $instance,
            'relationships' => $relationships,
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
     * @param Instance $instance
     * @return RedirectResponse
     */
    public function destroy(Instance $instance): RedirectResponse
    {
        $instance->delete();
        return redirect()->route('okapi-instances.index');
    }
}
