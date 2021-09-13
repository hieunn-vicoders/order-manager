<?php

namespace VCComponent\Laravel\Order\Policies;

use VCComponent\Laravel\Order\Contracts\OrderMailPolicyInterface;

class OrderMailPolicy implements OrderMailPolicyInterface
{
    public function ableToUse($user)
    {
        return true;
    }
}