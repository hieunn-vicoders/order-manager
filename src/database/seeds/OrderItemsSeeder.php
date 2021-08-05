<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use VCComponent\Laravel\Order\Entities\OrderItem;

class OrderItemsSeeder extends Seeder
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
            OrderItem::insert([
                [
                    "product_id" => rand(1, 10),
                    "quantity" =>rand(1, 5),
                    "price"=>rand(1, 10) * 100000,
                    "order_id" => rand(1, 1000),
                    "created_at" => $order_date,
                    "updated_at" => $order_date
                ]
            ]);
        }
    }
}
