<?php

namespace App\Policies;

class BotTradeInPricePolicy extends RoleBasedPolicy
{
    protected function viewPermission(): string
    {
        return 'bot.view';
    }

    protected function managePermission(): string
    {
        return 'bot.manage';
    }
}
