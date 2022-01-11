<?php

namespace VCComponent\Laravel\Order\Validators;

use VCComponent\Laravel\Vicoders\Core\Validators\AbstractValidator;
use VCComponent\Laravel\Vicoders\Core\Validators\ValidatorInterface;

class OrderValidator extends AbstractValidator implements OrderValidatorInterface
{
    protected $rules = [
        ValidatorInterface::RULE_ADMIN_CREATE => [
            'email' => ['bail', 'required', 'email'],
            'username' => ['required'],
            'phone_number' => ['required'],
            'address' => ['required'],
        ],
        ValidatorInterface::RULE_ADMIN_UPDATE => [
            'email' => ['bail', 'required', 'email'],
            'username' => ['required'],
            'phone_number' => ['required'],
            'address' => ['required'],
        ],
        ValidatorInterface::RULE_CREATE => [
            'email' => ['bail', 'required', 'email'],
            'username' => ['required'],
            'phone_number' => ['required'],
            'address' => ['required'],
        ],
        ValidatorInterface::UPDATE_STATUS_ITEM => [
            'status_id' => ['required'],
        ],
        "UPDATE_PAYMENT_STATUS" => [
            'payment_status' => ['required'],
        ],
        "CREATE_ORDER_ITEM" => [
            'product_id' => ['required'],
            'quantity' => ['required'],
        ],
        'RULE_EXPORT' => [
            'label' => ['required'],
            'extension' => ['required', 'regex:/(^xlsx$)|(^csv$)/'],
        ],
    ];
}
