<?php

namespace App\Http\Controllers\Okapi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Okapi\StoreTypeRequest;
use App\Http\Requests\Okapi\UpdateTypeRequest;
use App\Models\Okapi\Field;
use App\Models\Okapi\Type;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        return Inertia::render('Okapi/Type/List', [
            'types' => Type::all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(): Response
    {
        return Inertia::render('Okapi/Type/New', [
            'fieldTypes' => Field::TYPES,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreTypeRequest $request
     * @return RedirectResponse
     */
    public function store(StoreTypeRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            /** @var Type $contentType */
            $type = Type::query()->create(Arr::except($validated, ['fields']));

            foreach ($validated['fields'] as $field) {
                $field['okapi_type_id'] = $type->getAttribute('id');

                Field::query()->create($field);
            }
        });

        return redirect()->route('okapi-types.index');
    }

    /**
     * Display the specified resource.
     *
     * @param Type $type
     * @return Response
     */
    public function show(Type $type): Response
    {
        $type->load('fields');
        return Inertia::render('Okapi/Type/Show', [
            'fieldTypes' => Field::TYPES,
            'type' => $type,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Type $type
     * @return Response
     */
    public function edit(Type $type): Response
    {
        $type->load('fields');
        return Inertia::render('Okapi/Type/Edit', [
            'fieldTypes' => Field::TYPES,
            'type' => $type,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateTypeRequest $request
     * @param Type $type
     * @return RedirectResponse
     */
    public function update(UpdateTypeRequest $request, Type $type): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $type) {
            $type->update(Arr::except($validated, ['fields']));

            $type->fields()->whereNotIn('id',
                collect($validated['fields'])
                    ->filter(fn($field) => isset($field['id']))
                    ->map(fn($field) => $field['id'])
                    ->toArray()
            )->delete();

            foreach ($validated['fields'] as $field) {
                if (isset($field['id'])) {
                    Field::query()
                        ->where('id', $field['id'])
                        ->firstOrFail()
                        ->update(Arr::except($field, ['id']));
                } else {
                    $field['okapi_type_id'] = $type->getAttribute('id');
                    Field::query()->create($field);
                }
            }
        });

        return redirect()->route('okapi-types.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Type $type
     * @return RedirectResponse
     */
    public function destroy(Type $type): RedirectResponse
    {
        $type->delete();

        return redirect()->route('okapi-types.index');
    }
}
