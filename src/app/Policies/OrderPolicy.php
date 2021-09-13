<?php

namespace VCComponent\Laravel\Order\Policies;

use VCComponent\Laravel\Order\Contracts\OrderPolicyInterface;

class OrderPolicy implements OrderPolicyInterface
{
    public function ableToUse($user)
    {
        return true;
    }
}