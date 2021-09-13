<?php 

namespace VCComponent\Laravel\Order\Contracts;

interface OrderMailPolicyInterface
{
    public function ableToUse($user);
}