<?php

use Faker\Generator;
use VCComponent\Laravel\Order\Entities\OrderMail;

$factory->define(OrderMail::class, function (Generator $faker) {
    return [
        'email'  => $faker->email,
        'status' => 1,
    ];
});
