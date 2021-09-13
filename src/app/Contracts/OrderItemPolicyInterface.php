<?php 

namespace VCComponent\Laravel\Order\Contracts;

interface OrderItemPolicyInterface
{
    public function ableToUse($user);
}