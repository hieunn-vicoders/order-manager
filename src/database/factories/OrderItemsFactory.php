<?php

use Faker\Generator;
use VCComponent\Laravel\Order\Entities\OrderItem;

$factory->define(OrderItem::class, function (Generator $faker) {
    return [
        'product_id' => $faker->uuid,
        'quantity'   => $faker->randomNumber(),
        'price'      => $faker->randomNumber(),
        'order_id'   => $faker->uuid,
    ];
});
