<?php

use Illuminate\Database\Seeder;
use VCComponent\Laravel\Order\Entities\Customer;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Customer::class, 100)->create();
    }
}
