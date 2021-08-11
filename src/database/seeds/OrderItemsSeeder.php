<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use VCComponent\Laravel\Order\Entities\OrderItem;
use Illuminate\Support\Facades\DB;

class OrderItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $products = DB::table('products')->get();
        $product_ids = collect($products)->map(function ($item) {
            return $item->id;
        });

        $orders = DB::table('orders')->get();
        $order_items = collect($orders)->map(function ($item) {
            return $item->id;
        });
        for ($item = 0; $item < 1000; $item++) {
            $product_id = $product_ids[array_rand($product_ids->toArray())];
            $order_id = $order_items[array_rand($order_items->toArray())];
            $query_product = DB::table('products')->where('id', $product_id)->first();
            $query_order = DB::table('orders')->where('id', $order_id)->first();
            OrderItem::insert([
                [
                    "product_id" => $product_id,
                    "quantity" => rand(1, 5),
                    "price" => $query_product->price,
                    "order_id" => $order_id,
                    "created_at" => $query_order->created_at,
                    "updated_at" => $query_order->created_at
                ]
            ]);
        }
    }
}
