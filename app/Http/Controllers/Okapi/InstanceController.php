<?php

namespace App\Http\Controllers\Okapi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Okapi\StoreInstanceRequest;
use App\Http\Requests\Okapi\UpdateInstanceRequest;
use App\Models\Okapi\Instance;
use App\Models\Okapi\Type;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class InstanceController extends Controller
{
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

        return Inertia::render('Okapi/Instance/New', [
            'type' => $type,
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
        $fields = $type->fields()->pluck('id', 'slug')->toArray();

        DB::transaction(function () use ($type, $validated, $fields) {
            /** @var Instance $instance */
            $instance = Instance::query()->create([
                'okapi_type_id' => $type->id,
            ]);

            foreach ($validated as $key => $value) {
                $instance->fields()->attach($fields[$key], [
                    'value' => $value
                ]);
            }
        });

        return redirect()->route('okapi-instances.index', $type);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Instance $instance
     * @return Response
     */
    public function show(Instance $instance)
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
        $type->load('fields');
        $instance->load('values');

        return Inertia::render('Okapi/Instance/Edit', [
            'type' => $type,
            'instance' => $instance,
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
        $fields = $type->fields()->pluck('id', 'slug')->toArray();

        DB::transaction(function () use ($instance, $validated, $fields) {
            /** @var Instance $instance */
            $instance->fields()->detach();
            foreach ($validated as $key => $value) {
                $instance->fields()->attach($fields[$key], [
                    'value' => $value
                ]);
            }
        });

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
