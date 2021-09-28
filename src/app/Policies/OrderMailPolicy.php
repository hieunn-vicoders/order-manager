<?php

namespace VCComponent\Laravel\Order\Policies;

use VCComponent\Laravel\Order\Contracts\OrderMailPolicyInterface;

class OrderMailPolicy implements OrderMailPolicyInterface
{
    public function before($user, $ability)
    {
        if ($user->isAdministrator()) {
            return true;
        }
    }

    public function ableToUse($user)
    {
        return $user->hasPermission('manage-order-mail');
    }
}