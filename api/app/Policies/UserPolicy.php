<?php

namespace App\Policies;

class UserPolicy extends RoleBasedPolicy
{
    protected function viewPermission(): string
    {
        return 'users.view';
    }

    protected function managePermission(): string
    {
        return 'users.manage';
    }
}
