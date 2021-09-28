<?php

namespace VCComponent\Laravel\Order\Policies;

use VCComponent\Laravel\Order\Contracts\OrderItemPolicyInterface;

class OrderItemPolicy implements OrderItemPolicyInterface
{
    public function before($user, $ability)
    {
        if ($user->isAdministrator()) {
            return true;
        }
    }

    public function manage($user)
    {
        return $user->hasPermission('manage-order-item');
    }
}