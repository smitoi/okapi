<?php

namespace App\Http\Controllers\Okapi\Api;

use App\Http\Requests\Okapi\Auth\ApiRegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RegisterController extends ApiController
{
    public function __invoke(ApiRegisterRequest $request, Role $role): JsonResponse
    {
        $validated = $request->validated();
        $validated['password'] = Hash::make($validated['password']);
        /** @var User $user */
        $user = User::query()->create($validated);
        $user->assignRole($role);

        return $this->jsonSuccess(
            [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ], code: 201,
        );
    }
}
