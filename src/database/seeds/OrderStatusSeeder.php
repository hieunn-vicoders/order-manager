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
            ["name"=>"Chờ duyệt","slug" => "pending","status" => "is_active", "status_id" => "1"],
            ["name"=>"Đã duyệt","slug" => "approved","status" => "is_active", "status_id" => "2"],
            ["name"=>"Đang giao hàng","slug" => "delivery","status" => "is_active", "status_id" => "3"],
            ["name"=>"Hoàn thành","slug" => "complete","status" => "is_active", "status_id" => "4"],
            ["name"=>"Hủy đơn","slug" => "cancel","status" => "is_active", "status_id" => "5"],
            ["name"=>"Giao lại đơn","slug" => "return-order","status" => "is_active", "status_id" => "6"],
            ["name"=>"Khiếu nại","slug" => "complain","status" => "is_active", "status_id" => "7"],
            ["name"=>"Đang xử lý khiếu lại","slug" => "process-complain","status" => "is_active", "status_id" => "8"],

        ]);
    }
}
