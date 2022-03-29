<?php

namespace App\Http\Controllers\Okapi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Okapi\StoreTypeRequest;
use App\Http\Requests\Okapi\UpdateTypeRequest;
use App\Models\Okapi\Field;
use App\Models\Okapi\Relationship;
use App\Models\Okapi\Rule;
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
            'relationshipTypes' => Relationship::TYPES,
            'okapiTypes' => Type::query()->pluck('name', 'id'),
            'okapiTypesFields' => Field::query()->get()->groupBy('okapi_type_id')
                ->map(fn($field) => $field->pluck('name', 'id')),
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

        DB::transaction(static function () use ($validated) {
            /** @var Type $contentType */
            $type = Type::query()->create(Arr::except($validated, ['fields']));

            foreach ($validated['fields'] as $validatedField) {
                $validatedField['okapi_type_id'] = $type->getAttribute('id');

                $field = Field::query()->create($validatedField);

                foreach ($validatedField['rules'] as $ruleKey => $ruleValue) {
                    if ($ruleValue) {
                        Rule::query()->create([
                            'name' => $ruleKey,
                            'properties' => [
                                'value' => $ruleValue,
                            ],
                            'okapi_field_id' => $field->getAttribute('id'),
                        ]);
                    }
                }
            }

            foreach ($validated['relationships'] as $validatedRelationship) {
                $validatedRelationship['okapi_type_from_id'] = $type->getAttribute('id');

                $validatedRelationship['okapi_type_to_id'] = $validatedRelationship['to'];
                $validatedRelationship['okapi_field_display_id'] = $validatedRelationship['display'] ?? null;
                unset($validatedRelationship['to'], $validatedRelationship['store'], $validatedRelationship['display']);

                Relationship::query()->create($validatedRelationship);
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
        $type->load('fields', 'relationships');
        return Inertia::render('Okapi/Type/Show', [
            'fieldTypes' => Field::TYPES,
            'relationshipTypes' => Relationship::TYPES,
            'okapiTypes' => Type::query()->pluck('name', 'id'),
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
        $type->load('fields.rules', 'relationships');
        return Inertia::render('Okapi/Type/Edit', [
            'fieldTypes' => Field::TYPES,
            'relationshipTypes' => Relationship::TYPES,
            'okapiTypes' => Type::query()->pluck('name', 'id'),
            'okapiTypesFields' => Field::query()->get()->groupBy('okapi_type_id')
                ->map(fn($field) => $field->pluck('name', 'id')),
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

        DB::transaction(static function () use ($validated, $type) {
            $type->update(Arr::except($validated, ['fields']));

            $type->fields()->whereNotIn('id',
                collect($validated['fields'])
                    ->filter(fn($field) => isset($field['id']))
                    ->map(fn($field) => $field['id'])
                    ->toArray()
            )->delete();

            $type->relationships()->whereNotIn('id',
                collect($validated['relationships'])
                    ->filter(fn($field) => isset($field['id']))
                    ->map(fn($field) => $field['id'])
                    ->toArray()
            )->delete();

            foreach ($validated['fields'] as $validatedField) {
                if (isset($validatedField['id'])) {
                    $field = Field::query()
                        ->where('id', $validatedField['id'])
                        ->firstOrFail();
                    $field->update(Arr::except($validatedField, ['id', 'rules']));
                } else {
                    $validatedField['okapi_type_id'] = $type->getAttribute('id');
                    $field = Field::query()->create(Arr::except($validatedField, ['rules']));
                }

                /** @var Field $field */
                foreach ($validatedField['rules'] as $ruleKey => $ruleValue) {
                    $rule = Rule::query()
                        ->where('okapi_field_id', $field->id)
                        ->where('name', $ruleKey)->first();

                    if ($ruleValue) {
                        if ($rule) {
                            $rule->update([
                                'properties' => [
                                    'value' => $ruleValue
                                ],
                            ]);
                        } else {
                            Rule::query()->create([
                                'name' => $ruleKey,
                                'properties' => [
                                    'value' => $ruleValue,
                                ],
                                'okapi_field_id' => $field->getAttribute('id'),
                            ]);
                        }
                    } elseif ($rule) {
                        $rule->delete();
                    }
                }
            }

            foreach ($validated['relationships'] ?? [] as $validatedRelationship) {
                $validatedRelationship['okapi_type_to_id'] = $validatedRelationship['to'];
                $validatedRelationship['okapi_field_display_id'] = $validatedRelationship['display'] ?? null;
                unset($validatedRelationship['to'], $validatedRelationship['store'], $validatedRelationship['display']);

                if (isset($validatedRelationship['id'])) {
                    $relationship = Relationship::query()
                        ->where('id', $validatedRelationship['id'])
                        ->firstOrFail();
                    $relationship->update(Arr::except($validatedRelationship, ['id']));
                } else {
                    $validatedRelationship['okapi_type_from_id'] = $type->getAttribute('id');
                    Relationship::query()->create($validatedRelationship);
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
