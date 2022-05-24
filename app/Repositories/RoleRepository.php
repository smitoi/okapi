<?php

namespace App\Repositories;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class RoleRepository
{
    public function createRole(array $validated): Role
    {
        return DB::transaction(static function () use ($validated) {
            $validated['guard_name'] = 'web';
            /** @var Role $role */
            $role = Role::query()->create(Arr::except($validated, ['permissions']));
            $role->permissions()->attach($validated['permissions']);
            return $role;
        });
    }

    public function updateRole(array $validated, Role $role): Role
    {
        return DB::transaction(static function () use ($validated, $role) {
            $role->update(Arr::except($validated, ['permissions']));
            $role->permissions()->sync($validated['permissions']);
            return $role;
        });
    }
}
