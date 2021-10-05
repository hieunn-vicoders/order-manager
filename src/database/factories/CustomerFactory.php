<?php

use Faker\Generator;
use VCComponent\Laravel\Order\Entities\Customer;

$factory->define(Customer::class, function (Generator $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
        'phone_number' => $faker->e164PhoneNumber,
        'order_count' => $faker->numberBetween(1, 10),
        'total_amount' => $faker->numberBetween(500000, 1000000),
    ];
});
