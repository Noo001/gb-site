<?php

namespace App\Policies;

class BotActionLogPolicy extends RoleBasedPolicy
{
    protected function viewPermission(): string
    {
        return 'bot.view';
    }

    protected function managePermission(): string
    {
        return 'bot.manage';
    }

    public function create(\App\Models\User $user): bool
    {
        return false;
    }
}
