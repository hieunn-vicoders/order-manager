<?php

namespace VCComponent\Laravel\Order\Test\Feature\Order;

use Illuminate\Foundation\Testing\RefreshDatabase;
use VCComponent\Laravel\Order\Entities\Order;
use VCComponent\Laravel\Order\Entities\OrderItem;
use VCComponent\Laravel\Order\Entities\OrderStatus;
use VCComponent\Laravel\Order\Test\TestCase;
use VCComponent\Laravel\Product\Entities\Product;

class AdminOrderControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function can_get_order_list_by_admin_router()
    {
        $orders = factory(Order::class, 5)->create();
        $token = $this->loginToken();

        $orders = $orders->map(function ($e) {
            unset($e['updated_at']);
            unset($e['created_at']);
            return $e;
        })->toArray();

        $listIds = array_column($orders, 'id');
        array_multisort($listIds, SORT_DESC, $orders);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/order-management/admin/orders');

        $response->assertStatus(200);

        foreach ($orders as $item) {
            $this->assertDatabaseHas('orders', $item);
        }
    }

    /**
     * @test
     */
    public function can_create_order_by_admin_router()
    {
        $data = factory(Order::class)->make()->toArray();
        $token = $this->loginToken();
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('POST', 'api/order-management/admin/orders', $data);

        $response->assertStatus(500);
        $response->assertJson(['message' => 'Không thể tạo đơn hàng không có sản phẩm nào !']);

        $orderItems = factory(OrderItem::class)->make()->toArray();

        $product = factory(Product::class)->create();

        $orderItems['product_id'] = $product->id;
        $orderItems['quantity'] = 1;
        $data['order_items'] = [$orderItems];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('POST', 'api/order-management/admin/orders', $data);

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
    public function can_update_order_by_admin_router()
    {
        $order = factory(Order::class)->create();
        $token = $this->loginToken();

        unset($order['updated_at']);
        unset($order['created_at']);

        $id = $order->id;
        $order->email = 'emailUpdate@gmail.com';
        $data = $order->toArray();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('PUT', 'api/order-management/admin/orders/' . $id, $data);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'email' => 'emailUpdate@gmail.com',
            ],
        ]);

        $this->assertDatabaseHas('orders', $data);
    }

    /**
     * @test
     */
    public function can_delete_order_by_admin_router()
    {
        $order = factory(Order::class)->create()->toArray();
        $token = $this->loginToken();

        unset($order['updated_at']);
        unset($order['created_at']);

        $this->assertDatabaseHas('orders', $order);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('DELETE', 'api/order-management/admin/orders/' . $order['id']);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDeleted('orders', $order);
    }

    /**
     * @test
     */
    public function can_get_order_by_admin_router()
    {
        $order = factory(Order::class)->create();
        $token = $this->loginToken();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/order-management/admin/orders/' . $order->id);

        $response->assertStatus(200);
        $response->assertJson(['data' => [
            'customer_id' => $order['customer_id'],
            'email' => $order['email'],
            'phone_number' => $order['phone_number'],
        ],
        ]);
    }

    /**
     * @test
     */
    public function can_change_payment_status_order_by_admin_router()
    {
        $order = factory(Order::class)->make(['payment_status' => 0]);
        $token = $this->loginToken();

        $data = $order;
        $order->save();

        $payment_status = ['payment_status' => 5];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('PUT', 'api/order-management/admin/orders/' . $data->id . '/payment-status', $payment_status);

        $response->assertStatus(200);
        $response->assertJson(['success' => 'true']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/order-management/admin/orders/' . $data->id);
        $response->assertJson(['data' => ['payment_status' => 5]]);
    }

    /**
     * @test
     */
    public function can_change_status_order_by_admin_router()
    {
        factory(OrderStatus::class)->create(['slug' => 'pending', 'status_id' => 1]);
        factory(OrderStatus::class)->create(['slug' => 'approved', 'status_id' => 2]);

        $order = factory(Order::class)->make(['status_id' => 1]);
        $token = $this->loginToken();

        $data = $order;
        $order->save();

        $status = ['status_id' => 2];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('PUT', 'api/order-management/admin/orders/' . $data->id . '/status', $status);
        $response->assertStatus(200);
        $response->assertJson(['success' => 'true']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/order-management/admin/orders/' . $data->id);
        $response->assertJson(['data' => ['status_id' => 2]]);
    }

    /**
     * @test
     */
    public function can_export_order_by_admin_router()
    {
        $order = factory(Order::class)->create();
        $token = $this->loginToken();

        $data = [$order];
        $param = '?label=order&extension=xlsx';

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/order-management/admin/orders/exports' . $param);
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJson(['data' => [[
            "Số điện thoại" => $order->phone_number,
            "Email" => $order->email,
            "Địa chỉ chi tiết" => $order->address,
            "Ghi chú" => $order->order_note,
        ]]]);
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
