<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserRepository
{
    public function createUser(array $validated): User
    {
        return DB::transaction(static function () use ($validated) {
            $validated['password'] = Hash::make($validated['password']);

            /** @var User $user */
            $user = User::query()->create(Arr::except($validated, ['roles']));
            $user->roles()->detach();
            $user->assignRole($validated['roles']);
            return $user;
        });
    }

    public function updateUser(array $validated, User $user): User
    {
        return DB::transaction(static function () use ($validated, $user) {
            if (empty($validated['password']) || empty($validated['password_confirmation'])) {
                unset($validated['password'], $validated['password_confirmation']);
            }

            $user->update(Arr::except($validated, ['roles']));
            $user->roles()->detach();
            $user->assignRole($validated['roles']);
            return $user;
        });
    }
}
