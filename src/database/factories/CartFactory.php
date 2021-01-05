<?php

use Faker\Generator;
use VCComponent\Laravel\Order\Entities\Cart;

$factory->define(Cart::class, function (Generator $faker) {
    return [
        'uuid'  => $faker->uuid,
        'total' => 0,
    ];
});
