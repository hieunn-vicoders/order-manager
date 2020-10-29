<?php

namespace VCComponent\Laravel\Order\Http\Controllers\Web\Order;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use VCComponent\Laravel\Order\Contracts\ViewOrderControllerInterface;
use VCComponent\Laravel\Order\Traits\Helpers;
use VCComponent\Laravel\Order\ViewModels\Order\OrderViewModel;

class OrderController extends BaseController implements ViewOrderControllerInterface
{
    use Helpers;

    public function __construct()
    {
        if (isset(config('order.viewModels')['order'])) {
            $this->ViewModel = config('order.viewModels.order');
        } else {
            $this->ViewModel = OrderViewModel::class;
        }
    }

    public function index(Request $request)
    {
        $cart = getCart();
        
        $last_name = $request->session;

        $cartItemsCount = 0;
        // dd($last_name);
        if ($cart) {
            $cartItemsCount = $cart->cartItems->count();
        }
        
        if (!$cartItemsCount) {
            return redirect('cart');
        }
        // dd($last_name);
        $custom_view_data = $this->viewData($cart, $request);
        
        $view_model = new $this->ViewModel($cart);
        $data       = array_merge($custom_view_data, $view_model->toArray());
        // dd($data);
        return view($this->view(), $data)->with(compact($last_name));
    }

    protected function view()
    {
        return 'order::orderInfo';
    }

    protected function viewData($cart, Request $request)
    {
        return [];
    }

    protected function paymentInfo(Request $request)
    {
        $cart = getCart();

        $cartItemsCount = 0;
        // dd($cart);
        if ($cart) {
            $cartItemsCount = $cart->cartItems->count();
        }

        if (!$cartItemsCount) {
            return redirect('cart');
        }

        $custom_view_data = $this->viewData($cart, $request);
        // $_SESSION["last_name"] = $request->last_name;
        $view_model = new $this->ViewModel($cart);
        $data       = array_merge($custom_view_data, $view_model->toArray());
          
        return view($this->viewPaymentInfo(), $data);
    }

    protected function viewPaymentInfo()
    {
        return 'order::paymentInfo';
    }
}
