<?php

namespace App\Policies;

class SeoMetadataPolicy extends RoleBasedPolicy
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
