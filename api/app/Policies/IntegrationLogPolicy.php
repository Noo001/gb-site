<?php

namespace App\Policies;

class IntegrationLogPolicy extends RoleBasedPolicy
{
    protected function viewPermission(): string
    {
        return 'monitoring.view';
    }

    protected function managePermission(): string
    {
        return 'monitoring.manage';
    }

    public function create(\App\Models\User $user): bool
    {
        return false;
    }
}
