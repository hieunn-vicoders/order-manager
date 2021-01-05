<?php

namespace VCComponent\Laravel\Order\Test\Feature\Cart;

use Illuminate\Foundation\Testing\RefreshDatabase;
use VCComponent\Laravel\Order\Entities\Cart;
use VCComponent\Laravel\Order\Entities\CartItem;
use VCComponent\Laravel\Order\Test\TestCase;
use VCComponent\Laravel\Product\Entities\Product;

class FrontendCartControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function can_change_quantity_cart_item_by_frontend_router()
    {
        $order = factory(Cart::class)->create()->toArray();

        $product = factory(Product::class)->make(['quantity' => 5]);

        $maxQuantity = $product->quantity;
        $productName = $product->name;

        $product->save();

        $item = factory(CartItem::class)->make(['cart_id' => $order['uuid']])->save();

        $data = ['quantity' => $maxQuantity + 1];

        $response = $this->json('PUT', 'api/order-management/cart_item/1/quantity', $data);
        
        $response->assertJson(['error' => "Số lượng của sản phẩm " . $productName . " đã đạt đến giới hạn ! Bạn vẫn có thể đặt hàng nhưng với số lượng tối đa của sản phẩn này là : 5 . Xin lỗi vì sự bất tiện này !",
        ]);

        $data = ['quantity' => $maxQuantity];

        $response = $this->json('PUT', 'api/order-management/cart_item/1/quantity', $data);

        $response->assertJson(['result' => ['quantity' => 5]]);
    }
}
