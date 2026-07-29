<?php

namespace App\Policies;

class RegionPolicy extends RoleBasedPolicy
{
    protected function viewPermission(): string
    {
        return 'site.view';
    }

    protected function managePermission(): string
    {
        return 'site.manage';
    }
}
