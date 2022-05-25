<?php

namespace App\Http\Controllers\Okapi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Okapi\Type\StoreTypeRequest;
use App\Http\Requests\Okapi\Type\UpdateTypeRequest;
use App\Models\Okapi\Field;
use App\Models\Okapi\Relationship;
use App\Models\Okapi\Rule;
use App\Models\Okapi\Type;
use App\Repositories\InstanceRepository;
use App\Repositories\TypeRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TypeController extends Controller
{
    private TypeRepository $typeRepository;

    public function __construct(TypeRepository $typeRepository)
    {
        $this->typeRepository = $typeRepository;
    }

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
            'fieldTypes' => collect(Field::TYPES)->map(fn($item) => $item['name']),
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
        $this->typeRepository->createType($validated);
        return redirect()->route('okapi-types.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Type $type
     * @return Response
     */
    public function edit(Type $type): Response
    {
        $type->load('fields', 'relationships');
        return Inertia::render('Okapi/Type/Edit', [
            'fieldTypes' => collect(Field::TYPES)->map(fn($item) => $item['name']),
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
        $this->typeRepository->updateType($validated, $type);
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
