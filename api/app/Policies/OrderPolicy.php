<?php

namespace App\Policies;

class OrderPolicy extends RoleBasedPolicy
{
    protected function viewPermission(): string
    {
        return 'orders.view';
    }

    protected function managePermission(): string
    {
        return 'orders.manage';
    }
}
