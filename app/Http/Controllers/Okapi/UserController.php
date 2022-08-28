<?php

namespace App\Http\Controllers\Okapi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Okapi\User\StoreUserRequest;
use App\Http\Requests\Okapi\User\UpdateUserRequest;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Role;

class UserController extends Controller
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        return Inertia::render('Okapi/User/List', [
            'users' => User::all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(): Response
    {
        return Inertia::render('Okapi/User/New', [
            'roles' => Role::query()->where('name', '!=', Role::PUBLIC_ROLE)->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreUserRequest $request
     * @return RedirectResponse
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->userRepository->createUser($validated);
        return redirect()->route('okapi-users.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param User $user
     * @return Response
     */
    public function edit(User $user): Response
    {
        $user->load('roles');
        return Inertia::render('Okapi/User/Edit', [
            'user' => $user,
            'roles' => Role::query()->where('name', '!=', Role::PUBLIC_ROLE)->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateUserRequest $request
     * @param User $user
     * @return RedirectResponse
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();
        $this->userRepository->updateUser($validated, $user);
        return redirect()->route('okapi-users.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $user
     * @return RedirectResponse
     */
    public function destroy(User $user): RedirectResponse
    {
        if (User::query()->count() === 1) {
            abort(400);
        }

        $user->delete();
        return redirect()->route('okapi-users.index');
    }
}
