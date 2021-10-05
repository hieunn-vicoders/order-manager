<?php

namespace VCComponent\Laravel\Order\Policies;

use VCComponent\Laravel\Order\Contracts\OrderPolicyInterface;

class OrderPolicy implements OrderPolicyInterface
{
    public function before($user, $ability)
    {
        if ($user->isAdministrator()) {
            return true;
        }
    }

    public function ableToUse($user)
    {
        return $user->hasPermission('manage-order');
    }
}