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
    public function can_update_order_item_by_admin_router()
    {
        $order = $this->createOrder();
        $token = $this->loginToken();

        $orderItems = $order['orderItems']['data'][0];

        $quantity = ['quantity' => 5];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('PUT', 'api/order-management/admin/order_item/' . $order['id'] . '/product', $quantity);
        $response->assertStatus(200);
        $response->assertJson(['data' => [
            'id' => $orderItems['id'],
            'product_id' => $orderItems['product_id'],
            'quantity' => 5,
        ]]);
    }

    /**
     * @test
     */
    public function can_delete_order_item_by_admin_router()
    {
        $order = $this->createOrder();
        $orderItems = $order['orderItems']['data'][0];
        $token = $this->loginToken();

        unset($orderItems['timestamps']);
        $this->assertDatabaseHas('order_items', $orderItems);

        $quantity = ['quantity' => 5];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('DELETE', 'api/order-management/admin/order_item/' . $order['id'] . '/product', $quantity);

        $response->assertStatus(200);
        $response->assertJson(['success' => 'true']);

        $this->assertDeleted('order_items', $orderItems);
    }

    protected function createOrder()
    {
        $data = factory(Order::class)->make()->toArray();
        $token = $this->loginToken();

        $orderItems = factory(OrderItem::class)->make()->toArray();
        $product = factory(Product::class)->create();

        $orderItems['product_id'] = $product->id;
        $orderItems['quantity'] = 1;
        $data['order_items'] = [$orderItems];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('POST', 'api/order-management/admin/orders', $data);

        $order_id = $response->Json()['data']['id'];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/order-management/admin/orders/' . $order_id . '?includes=orderItems');
        return $response->Json()['data'];
    }
    protected function loginToken()
    {
        $dataLogin = ['username' => 'admin', 'password' => '123456789', 'email' => 'admin@test.com'];
        $user = factory(\VCComponent\Laravel\User\Entities\User::class)->make($dataLogin);
        $user->save();
        $login = $this->json('POST', 'api/user-management/login', $dataLogin);
        $token = $login->Json()['token'];
        return $token;
    }
}
