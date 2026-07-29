<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

abstract class RoleBasedPolicy
{
    use HandlesAuthorization;

    abstract protected function viewPermission(): string;

    abstract protected function managePermission(): string;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([$this->viewPermission(), $this->managePermission()]);
    }

    public function view(User $user, $model): bool
    {
        return $user->hasAnyPermission([$this->viewPermission(), $this->managePermission()]);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo($this->managePermission());
    }

    public function update(User $user, $model): bool
    {
        return $user->hasPermissionTo($this->managePermission());
    }

    public function delete(User $user, $model): bool
    {
        return $user->hasPermissionTo($this->managePermission());
    }

    public function restore(User $user, $model): bool
    {
        return $user->hasPermissionTo($this->managePermission());
    }

    public function forceDelete(User $user, $model): bool
    {
        return $user->hasPermissionTo($this->managePermission());
    }
}
