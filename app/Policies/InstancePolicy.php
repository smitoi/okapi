<?php

namespace App\Policies;

use App\Models\Okapi\Instance;
use App\Models\Okapi\Type;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class InstancePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @param Type $type
     * @return bool
     */
    public function viewAny(User $user, Type $type): bool
    {
        $permission = $type->permissions()->where('name', 'like', '%.list')->firstOrFail();
        return $user->hasPermissionTo($permission);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Instance $instance
     * @return bool
     */
    public function view(User $user, Instance $instance): bool
    {
        /** @var Type $type */
        $type = $instance->type()->first();
        $permission = $type->permissions()->where('name', 'like', '%.view')->firstOrFail();
        return $user->hasPermissionTo($permission);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @param Type $type
     * @return bool
     */
    public function create(User $user, Type $type): bool
    {
        $permission = $type->permissions()->where('name', 'like', '%.create')->firstOrFail();
        return $user->hasPermissionTo($permission);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Instance $instance
     * @return bool
     */
    public function update(User $user, Instance $instance): bool
    {
        /** @var Type $type */
        $type = $instance->type()->first();
        $permission = $type->permissions()->where('name', 'like', '%.edit')->firstOrFail();
        return $user->hasPermissionTo($permission);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Instance $instance
     * @return bool
     */
    public function delete(User $user, Instance $instance): bool
    {
        /** @var Type $type */
        $type = $instance->type()->first();
        $permission = $type->permissions()->where('name', 'like', '%.delete')->firstOrFail();
        return $user->hasPermissionTo($permission);
    }
}
