<?php

namespace VCComponent\Laravel\Order\Test\Feature\Order;

use Illuminate\Foundation\Testing\RefreshDatabase;
use VCComponent\Laravel\Order\Entities\Order;
use VCComponent\Laravel\Order\Entities\OrderItem;
use VCComponent\Laravel\Order\Test\TestCase;
use VCComponent\Laravel\Product\Entities\Product;

class FrontendOrderControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function can_get_order_list_of_user_by_front_end_router()
    {
        $order = factory(Order::class)->make(['customer_id' => 2]);

        $data = [$order->toArray()];
        $order->save();

        $response = $this->call('GET', 'api/order-management/users/1/orders');

        $response->assertStatus(200);
        $response->assertJson(['data' => []]);

        $response = $this->call('GET', 'api/order-management/users/2/orders');
        $response->assertStatus(200);

        $response->assertJson(['data' => $data]);
    }

    /**
     * @test
     */
    public function can_create_order_by_front_end_router()
    {
        $data = factory(Order::class)->make()->toArray();

        $response = $this->json('POST', 'api/order-management/orders', $data);

        $response->assertStatus(500);
        $response->assertJson(['message' => 'Không thể tạo đơn hàng không có sản phẩm nào !']);

        $orderItems = factory(OrderItem::class)->make()->toArray();

        $product = factory(Product::class)->create();

        $orderItems['product_id'] = $product->id;
        $orderItems['quantity'] = 1;
        $data['order_items'] = [$orderItems];

        $response = $this->json('POST', 'api/order-management/orders', $data);

        $response->assertStatus(200);
        $response->assertJson(['data' => [
            'customer_id' => $data['customer_id'],
            'email' => $data['email'],
            'phone_number' => $data['phone_number'],
        ],
        ]);

        unset($data['order_items']);
        $this->assertDatabaseHas('orders', $data);
    }

    /**
     * @test
     */
    public function can_get_order_by_front_end_router()
    {
        $order = factory(Order::class)->create();
        $response = $this->call('GET', 'api/order-management/orders/' . $order->id);
        $response->assertStatus(200);
        $response->assertJson(['data' => [
            'customer_id' => $order['customer_id'],
            'email' => $order['email'],
            'phone_number' => $order['phone_number'],
        ],
        ]);
    }
}
