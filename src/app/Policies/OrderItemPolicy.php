<?php

namespace VCComponent\Laravel\Order\Policies;

use VCComponent\Laravel\Order\Contracts\OrderItemPolicyInterface;

class OrderItemPolicy implements OrderItemPolicyInterface
{
    public function ableToUse($user)
    {
        return true;
    }
}