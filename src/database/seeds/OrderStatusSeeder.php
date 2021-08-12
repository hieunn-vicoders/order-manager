<?php
use Illuminate\Database\Seeder;
use VCComponent\Laravel\Order\Entities\OrderStatus;
class OrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        OrderStatus::insert([
            ["name"=>"Chờ duyệt","slug" => "pending","status" => "1"],
            ["name"=>"Đã duyệt","slug" => "approved","status" => "1"],
            ["name"=>"Đang giao hàng","slug" => "delivery","status" => "1"],
            ["name"=>"Hoàn thành","slug" => "complete","status" => "1"],
            ["name"=>"Hủy đơn","slug" => "cancel","status" => "1"],
            ["name"=>"Giao lại đơn","slug" => "return-order","status" => "1"],
            ["name"=>"Khiếu nại","slug" => "complain","status" => "1"],
            ["name"=>"Đang xử lý khiếu lại","slug" => "process-complain","status" => "1"],

        ]);
    }
}
