<?php

namespace App\Policies;

use App\Models\Okapi\Instance;
use App\Models\Okapi\Type;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Spatie\Permission\Models\Permission;

class InstancePolicy
{
    use HandlesAuthorization;

    private function checkUserOrPublic(?User $user, Permission $permission): bool
    {
        if ($user) {
            return $user->hasPermissionTo($permission);
        }

        /** @var Role $role */
        $role = Role::query()->where('name', Role::PUBLIC_ROLE)->firstOrFail();
        return $role->hasPermissionTo($permission);
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param User|null $user
     * @param Type $type
     * @return bool
     */
    public function viewAny(?User $user, Type $type): bool
    {
        /** @var Permission $permission */
        $permission = $type->permissions()->where('name', 'like', '%.list')->firstOrFail();
        return $this->checkUserOrPublic($user, $permission);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User|null $user
     * @param Type $type
     * @return bool
     */
    public function view(?User $user, Type $type): bool
    {
        /** @var Permission $permission */
        $permission = $type->permissions()->where('name', 'like', '%.view')->first();
        return $this->checkUserOrPublic($user, $permission);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User|null $user
     * @param Type $type
     * @return bool
     */
    public function create(?User $user, Type $type): bool
    {
        /** @var Permission $permission */
        $permission = $type->permissions()->where('name', 'like', '%.create')->firstOrFail();
        return $this->checkUserOrPublic($user, $permission);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User|null $user
     * @param Type $type
     * @return bool
     */
    public function update(?User $user, Type $type): bool
    {
        /** @var Permission $permission */
        $permission = $type->permissions()->where('name', 'like', '%.edit')->firstOrFail();
        return $this->checkUserOrPublic($user, $permission);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User|null $user
     * @param Type $type
     * @return bool
     */
    public function delete(?User $user, Type $type): bool
    {
        /** @var Permission $permission */
        $permission = $type->permissions()->where('name', 'like', '%.delete')->firstOrFail();
        return $this->checkUserOrPublic($user, $permission);
    }
}
