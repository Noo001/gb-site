<?php

namespace App\Policies;

class CategoryPolicy extends RoleBasedPolicy
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
