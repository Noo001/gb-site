<?php

namespace App\Policies;

class BotKnowledgePolicy extends RoleBasedPolicy
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
