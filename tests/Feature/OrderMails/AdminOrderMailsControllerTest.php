<?php

namespace VCComponent\Laravel\Order\Test\Feature\OrderMails;

use Illuminate\Foundation\Testing\RefreshDatabase;
use VCComponent\Laravel\Order\Entities\OrderMail;
use VCComponent\Laravel\Order\Test\TestCase;

class AdminOrderMailsControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function can_get_order_mails_list_by_admin_router()
    {
        $orders = factory(OrderMail::class, 5)->create();
        $token = $this->loginToken();

        $orders = $orders->map(function ($e) {
            unset($e['updated_at']);
            unset($e['created_at']);
            return $e;
        })->toArray();

        $listIds = array_column($orders, 'id');
        array_multisort($listIds, SORT_DESC, $orders);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/order-management/admin/orderMails');

        $response->assertStatus(200);

        foreach ($orders as $item) {
            $this->assertDatabaseHas('order_mails', $item);
        }
    }

    /**
     * @test
     */
    public function can_create_order_mail_by_admin_router()
    {
        $data = factory(OrderMail::class)->make()->toArray();
        $token = $this->loginToken();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('POST', 'api/order-management/admin/orderMails', $data);

        $response->assertStatus(200);
        $response->assertJson(['data' => [
            'email' => $data['email'],
            'status' => $data['status'],
        ],
        ]);

        $this->assertDatabaseHas('order_mails', $data);
    }

    /**
     * @test
     */
    public function can_update_order_mails_by_admin_router()
    {
        $data = factory(OrderMail::class)->create()->toArray();
        $token = $this->loginToken();

        unset($data['updated_at']);
        unset($data['created_at']);

        $this->assertDatabaseHas('order_mails', $data);

        $email = ['email' => 'udpate@gmail.com'];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('PUT', 'api/order-management/admin/orderMails/' . $data['id'], $email);

        $response->assertStatus(200);
        $response->assertJson(['data' => [
            'email' => $email['email'],
            'status' => $data['status'],
        ],
        ]);
    }

    /**
     * @test
     */
    public function can_delete_order_mail_by_admin_router()
    {
        $order = factory(OrderMail::class)->create()->toArray();
        $token = $this->loginToken();

        unset($order['updated_at']);
        unset($order['created_at']);

        $this->assertDatabaseHas('order_mails', $order);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('DELETE', 'api/order-management/admin/orderMails/' . $order['id']);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDeleted('order_mails', $order);
    }

    /**
     * @test
     */
    public function can_get_order_mail_by_admin_router()
    {
        $order = factory(OrderMail::class)->create();
        $token = $this->loginToken();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/order-management/admin/orderMails/' . $order->id);

        $response->assertStatus(200);
        $response->assertJson(['data' => [
            'email' => $order['email'],
        ],
        ]);
    }

    /**
     * @test
     */
    public function can_update_status_order_mail_by_admin_router()
    {
        $order = factory(OrderMail::class)->make(['status' => 0]);
        $data = $order;
        $order->save();
        $token = $this->loginToken();

        $status = ['status' => 5];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('PUT', 'api/order-management/admin/orderMails/' . $data->id . '/status', $status);

        $response->assertStatus(200);
        $response->assertJson(['success' => 'true']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/order-management/admin/orderMails/' . $data->id);
        $response->assertJson(['data' => ['status' => 5]]);
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
