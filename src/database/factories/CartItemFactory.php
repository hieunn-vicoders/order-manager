<?php

use Faker\Generator;
use VCComponent\Laravel\Order\Entities\CartItem;

$factory->define(CartItem::class, function (Generator $faker) {
    return [
        'cart_id'    => 1,
        'product_id' => 1,
        'quantity'   => 1,
        'price'      => 1,
        'amount'     => 1,
    ];
});
