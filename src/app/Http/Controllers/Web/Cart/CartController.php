<?php

namespace VCComponent\Laravel\Order\Http\Controllers\Web\Cart;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use VCComponent\Laravel\Order\Actions\CartItem\ChangeCartItemQuantityAction;
use VCComponent\Laravel\Order\Actions\CartItem\CreateCartItemAction;
use VCComponent\Laravel\Order\Actions\CartItem\DeleteCartItemAction;
use VCComponent\Laravel\Order\Contracts\ViewCartControllerInterface;
use VCComponent\Laravel\Order\Entities\CartProductAttribute;
use VCComponent\Laravel\Order\Traits\Helpers;
use VCComponent\Laravel\Order\ViewModels\Cart\CartViewModel;
use VCComponent\Laravel\Product\Entities\ProductAttribute;

class CartController extends BaseController implements ViewCartControllerInterface
{
    use Helpers;

    protected $action_change;
    protected $action_create;
    protected $action_delete;
    public function __construct(ChangeCartItemQuantityAction $action_change, CreateCartItemAction $action_create, DeleteCartItemAction $action_delete)
    {
        if (isset(config('order.viewModels')['cart'])) {
            $this->ViewModel = config('order.viewModels.cart');
        } else {
            $this->ViewModel = CartViewModel::class;
        }

        $this->action_change = $action_change;
        $this->action_create = $action_create;
        $this->action_delete = $action_delete;

    }

    public function index(Request $request)
    {
        $type = $this->getTypeCart($request);

        $cart = getCart();

        $custom_view_data = $this->viewData($cart, $request);

        $view_model = new $this->ViewModel($cart);
        $data = array_merge($custom_view_data, $view_model->toArray());

        return view($this->view(), $data);

    }

    protected function view()
    {
        return 'pages.cart';
    }

    protected function viewData($cart, Request $request)
    {
        return [];
    }

    public function changCartItemQuantity($id, Request $request)
    {
        $data = [
            'id' => $id,
            'quantity' => $request->input('quantity'),
        ];

        $this->action_change->execute($data);

        return redirect('cart');
    }

    public function createCartItem(Request $request)
    {
        $cart_id = getCart()->uuid;
        $product_id = $request->get('product_id');
        $product_price = $request->get('product_price');
        $attributes = $this->getAttributes($request);

        $data = [
            'cart_id' => $cart_id,
            'product_id' => $product_id,
            'quantity' => $request->get('quantity'),
            'price' => $product_price,
        ];

        if ($attributes != null) {
            $data['attributes'] = $attributes;
            $data['price'] = $this->hasAttributes($data);
        }
        $cart_item = $this->action_create->execute($data, $request);

        if ($attributes != null) {
            foreach ($attributes as $key => $attribute) {
                $data_attributes = [
                    'cart_item_id' => (int) $cart_item->id,
                    'product_id' => (int) $request->get('product_id'),
                    'value_id' => (int) $attribute,
                ];

                CartProductAttribute::firstOrCreate($data_attributes);
            }
        }

        if (isset($alert)) {
            $response = back()->with('error', __($alert));
        } else {
            $response = back()->with('messages', __('Sản phẩm đã được thêm vào giỏ hàng'));
            if ($request->has('redirect')) {
                $response = redirect($request->get('redirect'));
            }
        }

        return $response;
    }

    protected function getAttributes($request)
    {
        $data = $request->all();
        $cart_data = [
            'quantity' => $request->get('quantity'),
            'product_id' => $request->get('product_id'),
            'product_price' => $request->get('product_price'),
        ];

        if ($request->has('redirect')) {
            $cart_data['redirect'] = $request->get('redirect');
        }

        $attributes = array_diff_assoc($data, $cart_data);

        unset($attributes['_token']);

        return $attributes;
    }

    protected function hasAttributes($data)
    {
        $sold_price = $data['price'];
        if ($data['attributes'] !== null) {
            $product_attributes = ProductAttribute::select('type', 'price')->where('product_id', $data['product_id'])->whereIn('value_id', $data['attributes'])->get();

            $caculate_attributes_price = $product_attributes->sum(function ($q) {
                if ($q->type === 2) {
                    $total = -$q->price;
                } else if ($q->type === 3) {
                    $total = 0;
                } else {
                    $total = $q->price;
                }
                return $total;
            });
            $special_price = $product_attributes->sum(function ($q) {
                return $q->type === 3 ? $q->price : 0;
            });

            if ($special_price !== 0) {
                $sold_price = $special_price + $caculate_attributes_price;
            } else {
                $sold_price = $data['price'] + $caculate_attributes_price;
            }
        }
        return $sold_price;
    }
    public function deleteCartItem($id)
    {
        $this->action_delete->execute($id);
        return redirect('cart');
    }
}
