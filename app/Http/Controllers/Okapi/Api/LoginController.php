<?php

namespace App\Http\Controllers\Okapi\Api;

use App\Http\Requests\Okapi\Auth\ApiLoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class LoginController extends ApiController
{
    public function __invoke(ApiLoginRequest $request, Role $role): JsonResponse
    {
        $validated = $request->validated();

        if (Auth::validate($validated)) {
            /** @var User $user */
            $user = User::query()->where('email', $validated['email'])->firstOrFail();

            if ($user->hasRole($role)) {
                return $this->jsonSuccess([
                    'token' => $user->createToken($request->getClientIp())->plainTextToken,
                ]);
            }
        }

        return $this->jsonError(
            code: 400,
        );
    }
}
