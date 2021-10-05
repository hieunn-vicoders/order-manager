<?php

use Faker\Generator as Faker;
use VCComponent\Laravel\Order\Entities\OrderStatus;

$factory->define(OrderStatus::class, function (Faker $faker) {
    return [
        'name' => $faker->words(rand(3, 5), true),
        'name' => 'slug',
        'is_active' => 'is_active',
        'status_id' => 1,
    ];
});
