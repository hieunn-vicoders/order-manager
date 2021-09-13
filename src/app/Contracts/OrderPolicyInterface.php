<?php 

namespace VCComponent\Laravel\Order\Contracts;

interface OrderPolicyInterface
{
    public function ableToUse($user);
}