<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use VCComponent\Laravel\Order\Entities\Order;

class OrdersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        for ($item = 0; $item < 1000; $item++) {
            $date = Carbon::create(2021, 8, 5, 0);
            $order_date = $date->subDay(rand(1, 365));
            Order::insert([
                [
                    "customer_id" => "2",
                    "total" => rand(1, 10000) * 1000,
                    "phone_number" => "",
                    "username" => "",
                    "address" => "",
                    "payment_method" => 1,
                    "payment_status" => 1,
                    "status_id" => rand(1, 8),
                    "status" => 'is_active',
                    "order_date" => $order_date,
                    "created_at" => $order_date,
                    "updated_at" => $order_date,
                ],
            ]);
        }
    }
}
