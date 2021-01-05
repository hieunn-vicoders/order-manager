<?php

namespace VCComponent\Laravel\Order\Test\Feature\OrderItems;

use Illuminate\Foundation\Testing\RefreshDatabase;
use VCComponent\Laravel\Order\Entities\Order;
use VCComponent\Laravel\Order\Entities\OrderItem;
use VCComponent\Laravel\Order\Test\TestCase;
use VCComponent\Laravel\Product\Entities\Product;

class AdminOrderItemsControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function can_uppate_order_item_by_admin_router()
    {
        $order      = $this->createOrder();
        $orderItems = $order['orderItems']['data'][0];

        $quantity = ['quantity' => 5];
        $response = $this->call('PUT', 'api/order-management/admin/order_item/' . $order['id'] . '/product', $quantity);

        $response->assertStatus(200);
        $response->assertJson(['data' => [
            'id'         => $orderItems['id'],
            'product_id' => $orderItems['product_id'],
            'quantity'   => 5,
        ]]);
    }

    /**
     * @test
     */
    public function can_delete_order_item_by_admin_router()
    {
        $order      = $this->createOrder();
        $orderItems = $order['orderItems']['data'][0];

        unset($orderItems['timestamps']);
        $this->assertDatabaseHas('order_items', $orderItems);

        $quantity = ['quantity' => 5];
        $response = $this->call('DELETE', 'api/order-management/admin/order_item/' . $order['id'] . '/product', $quantity);

        $response->assertStatus(200);
        $response->assertJson(['success' => 'true']);

        $this->assertDeleted('order_items', $orderItems);
    }

    protected function createOrder()
    {
        $data = factory(Order::class)->make()->toArray();

        $orderItems = factory(OrderItem::class)->make()->toArray();
        $product = factory(Product::class)->create();

        $orderItems['product_id'] = $product->id;
        $orderItems['quantity']   = 1;
        $data['order_items']      = [$orderItems];

        $response = $this->json('POST', 'api/order-management/admin/orders', $data);

        $order_id = $response->Json()['data']['id'];

        $response = $this->call('GET', 'api/order-management/admin/orders/' . $order_id . '?includes=orderItems');
        return $response->Json()['data'];
    }
}
