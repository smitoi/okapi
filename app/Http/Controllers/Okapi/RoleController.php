<?php

namespace App\Http\Controllers\Okapi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Okapi\Role\StoreRoleRequest;
use App\Http\Requests\Okapi\Role\UpdateRoleRequest;
use App\Models\Okapi\Type;
use App\Repositories\RoleRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use App\Models\Role;

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
        return Inertia::render('Okapi/Role/List', [
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
        return Inertia::render('Okapi/Role/New', [
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
     * @param Role $role
     * @return Response
     */
    public function show(Role $role): Response
    {
        if ($role->name === Role::ADMIN_ROLE) {
            abort(400);
        }

        $role->load('permissions');

        return Inertia::render('Okapi/Role/Show', [
            'role' => $role,
            'permissions' => Permission::all(),
            'types' => Type::all(),
        ]);
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

        if ($role->name === Role::ADMIN_ROLE) {
            abort(400);
        }

        return Inertia::render('Okapi/Role/Edit', [
            'role' => $role,
            'permissions' => Permission::all(),
            'types' => Type::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateRoleRequest $request
     * @param Role $role
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
     * @param Role $role
     * @return RedirectResponse
     */
    public function destroy(Role $role): RedirectResponse
    {
        if (in_array($role->name, Role::PROTECTED_ROLES, true)) {
            abort(400);
        }

        $role->delete();
        return redirect()->route('okapi-roles.index');
    }
}
