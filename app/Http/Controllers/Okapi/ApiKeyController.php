<?php

namespace App\Http\Controllers\Okapi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Okapi\ApiKey\StoreApiKeyRequest;
use App\Http\Requests\Okapi\ApiKey\UpdateApiKeyRequest;
use App\Models\Okapi\ApiKey;
use App\Models\Okapi\Type;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;

class ApiKeyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        return Inertia::render('Okapi/ApiKey/List', [
            'apiKeys' => ApiKey::all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(): Response
    {
        return Inertia::render('Okapi/ApiKey/New', [
            'permissions' => Permission::all(),
            'types' => Type::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreApiKeyRequest $request
     * @return RedirectResponse
     */
    public function store(StoreApiKeyRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $token = Str::random(ApiKey::API_KEY_LENGTH);

        /** @var ApiKey $apiKey */
        $apiKey = ApiKey::query()->create([
            'name' => $validated['name'],
            'token' => Hash::make($token),
        ]);
        $apiKey->permissions()->attach($validated['permissions']);

        return redirect()->route('okapi-api-keys.show', $apiKey->id)->with('plaintext-token', $token);
    }

    /**
     * Display the specified resource.
     *
     * @param ApiKey $apiKey
     * @return Response
     */
    public function show(ApiKey $apiKey): Response
    {
        $token = request()?->session()->get('plaintext-token');
        if ($token) {
            $apiKey->setAttribute('plaintext-token', $token);
        }

        $apiKey->load('permissions');

        return Inertia::render('Okapi/ApiKey/Show', [
            'apiKey' => $apiKey,
            'permissions' => Permission::all(),
            'types' => Type::all(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param ApiKey $apiKey
     * @return Response
     */
    public function edit(ApiKey $apiKey): Response
    {
        $apiKey->load('permissions');

        return Inertia::render('Okapi/ApiKey/Edit', [
            'apiKey' => $apiKey,
            'permissions' => Permission::all(),
            'types' => Type::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateApiKeyRequest $request
     * @param ApiKey $apiKey
     * @return RedirectResponse
     */
    public function update(UpdateApiKeyRequest $request, ApiKey $apiKey): RedirectResponse
    {
        $validated = $request->validated();

        $apiKey->update([
            'name' => $validated['name'],
        ]);
        $apiKey->permissions()->attach($validated['permissions']);

        return redirect()->route('okapi-api-keys.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param ApiKey $apiKey
     * @return RedirectResponse
     */
    public function destroy(ApiKey $apiKey): RedirectResponse
    {
        $apiKey->delete();
        return redirect()->route('okapi-api-keys.index');
    }
}
