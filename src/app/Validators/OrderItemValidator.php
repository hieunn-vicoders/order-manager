<?php

namespace VCComponent\Laravel\Order\Validators;

use VCComponent\Laravel\Vicoders\Core\Validators\AbstractValidator;
use VCComponent\Laravel\Vicoders\Core\Validators\ValidatorInterface;

class OrderItemValidator extends AbstractValidator implements OrderItemValidatorInterface
{
    protected $rules = [
        ValidatorInterface::RULE_ADMIN_CREATE => [
            'quantity'   => ['required'],
        ],
        ValidatorInterface::RULE_ADMIN_UPDATE => [
            'quantity'   => ['required'],
        ],
    ];
}
