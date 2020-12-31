<?php

use Faker\Generator;
use VCComponent\Laravel\Order\Entities\Order;

$factory->define(Order::class, function (Generator $faker) {
    return [
        'user_id'        => 1,
        'email'          => $faker->email,
        'username'       => $faker->userName,
        'phone_number'   => $faker->e164PhoneNumber,
        'address'        => $faker->address,
        'order_note'     => $faker->sentences(rand(4, 7), true),
        'payment_method' => $faker->randomNumber(),
    ];
});
