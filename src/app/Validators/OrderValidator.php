<?php

namespace VCComponent\Laravel\Order\Validators;

use VCComponent\Laravel\Vicoders\Core\Validators\AbstractValidator;
use VCComponent\Laravel\Vicoders\Core\Validators\ValidatorInterface;

class OrderValidator extends AbstractValidator
{
    protected $rules = [
        ValidatorInterface::RULE_ADMIN_CREATE => [
            'customer_id' => ['required'],
            'email' => ['bail', 'required', 'email'],
            'username' => ['required'],
            'phone_number' => ['required'],
            'address' => ['required'],
            'order_note' => ['required'],
            'payment_method' => ['required'],
        ],
        ValidatorInterface::RULE_ADMIN_UPDATE => [
            'customer_id' => ['required'],
            'email' => ['bail', 'required', 'email'],
            'username' => ['required'],
            'phone_number' => ['required'],
            'address' => ['required'],
            'order_note' => ['required'],
            'payment_method' => ['required'],
        ],
        ValidatorInterface::RULE_CREATE => [
            'customer_id' => ['required'],
            'email' => ['bail', 'required', 'email'],
            'username' => ['required'],
            'phone_number' => ['required'],
            'address' => ['required'],
            'order_note' => ['required'],
            'payment_method' => ['required'],
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
