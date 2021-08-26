<?php

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
        factory(Order::class, 1000)->create();
    }
}
