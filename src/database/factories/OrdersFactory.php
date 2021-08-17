<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use VCComponent\Laravel\Order\Entities\Order;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
$fakerVN = \Faker\Factory::create('vi_VN');
$factory->define(Order::class, function (Faker $faker) use ($fakerVN) {
    $customers = DB::table('customers')->get();
    $customer_items = collect($customers)->map(function ($item) {
        return $item->id;
    });
    $customer_id = $customer_items[array_rand($customer_items->toArray())];
    $query_customer = DB::table('customers')->where('id', $customer_id)->first();
    $date = Carbon::create(2021, 8, 5, 0);
    $order_date = $date->subDay(rand(1, 365));
    return [
        "customer_id" => $customer_id,
        "total" => rand(1, 10000) * 1000,
        "phone_number" => $query_customer->phone_number,
        "username" => $query_customer->name,
        'email' => $query_customer->email,
        'address' => $fakerVN->address,
        "payment_method" => 1,
        "payment_status" => 1,
        "status_id" => rand(1, 8),
        "is_active" => 1,
        "order_date" => $order_date,
        "created_at" => $order_date,
        "updated_at" => $order_date

    ];
});
