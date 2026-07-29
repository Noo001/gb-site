<?php

namespace App\Policies;

class StorePolicy extends RoleBasedPolicy
{
    protected function viewPermission(): string
    {
        return 'catalog.view';
    }

    protected function managePermission(): string
    {
        return 'catalog.manage';
    }
}
