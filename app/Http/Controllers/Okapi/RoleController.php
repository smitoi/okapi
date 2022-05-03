<?php

namespace App\Http\Controllers\Okapi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Okapi\Role\StoreRoleRequest;
use App\Http\Requests\Okapi\Role\UpdateRoleRequest;
use App\Models\Okapi\Type;
use App\Repositories\RoleRepository;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    private RoleRepository $roleRepository;

    public function __construct(RoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        return Inertia::render('Okapi/Roles/List', [
            'roles' => Role::all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(): Response
    {
        return Inertia::render('Okapi/Roles/New', [
            'permissions' => Permission::all(),
            'types' => Type::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRoleRequest $request
     * @return RedirectResponse
     */
    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->roleRepository->createRole($validated);
        return redirect()->route('okapi-roles.index');
    }

    /**
     * Display the specified resource.
     *
     * @param Type $type
     * @return Response
     */
    public function show(Type $type): Response
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Role $role
     * @return Response
     */
    public function edit(Role $role): Response
    {
        $role->load('permissions');

        return Inertia::render('Okapi/Roles/Edit', [
            'role' => $role,
            'permissions' => Permission::all(),
            'types' => Type::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateRoleRequest $request
     * @param Type $type
     * @return RedirectResponse
     */
    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $validated = $request->validated();
        $this->roleRepository->updateRole($validated, $role);
        return redirect()->route('okapi-roles.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Type $type
     * @return RedirectResponse
     */
    public function destroy(Type $type): RedirectResponse
    {

    }
}
