<?php

namespace VCComponent\Laravel\Order\Http\Controllers\Web\Order;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use VCComponent\Laravel\Order\Actions\Order\CreateOrderAction;
use VCComponent\Laravel\Order\Contracts\ViewOrderControllerInterface;
use VCComponent\Laravel\Order\Entities\Cart;
use VCComponent\Laravel\Order\Entities\Order;
use VCComponent\Laravel\Order\Entities\OrderMail;
use VCComponent\Laravel\Order\Mail\MailNotify;
use VCComponent\Laravel\Order\Repositories\OrderRepository;
use VCComponent\Laravel\Order\Traits\Helpers;
use VCComponent\Laravel\Order\ViewModels\Order\OrderViewModel;
use VCComponent\Laravel\Payment\Actions\PaymentAction;

class OrderController extends BaseController implements ViewOrderControllerInterface
{
    use Helpers;

    public function __construct(OrderRepository $repository, CreateOrderAction $create_order, PaymentAction $payment)
    {
        if (isset(config('order.viewModels')['order'])) {
            $this->ViewModel = config('order.viewModels.order');
        } else {
            $this->ViewModel = OrderViewModel::class;
        }
        $this->repository = $repository;
        $this->create_order = $create_order;
        $this->payment = $payment;
    }

    public function index(Request $request)
    {
        $cart = getCart();

        $cartItemsCount = 0;

        if ($cart) {
            $cartItemsCount = $cart->cartItems->count();
        }

        if (!$cartItemsCount) {
            return redirect('cart');
        }

        $custom_view_data = $this->viewData($cart, $request);

        $view_model = new $this->ViewModel($cart);
        $data = array_merge($custom_view_data, $view_model->toArray());

        return view($this->view(), $data);
    }

    protected function view()
    {
        return 'order::orderInfo';
    }

    protected function viewData($cart, Request $request)
    {
        return [];
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'last_name' => 'required',
            'address' => 'required',
            'phone_number' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        if ($request->input('note') == null) {
            $order_note = 'Thanh toán : ' . $request->input('phone_number');
        } else {
            $order_note = $request->input('note');
        }
        $total = $this->repository->usePromoCodeWeb($request);
        $data = [
            'username' => $request->input('first_name') . " " . $request->input('last_name'),
            'phone_number' => $request->input('phone_number'),
            'email' => $request->input('email'),
            'address' => $request->input('address'),
            'total' => $total,
            'order_note' => $order_note,
            'payment_method' => $request->input('payment_method'),
            'cart_id' => $request->input('cart_id'),
        ];

        if ($request->has(['district', 'provine'])) {
            $data['district'] = $request->input('district');
            $data['provine'] = $request->input('provine');
        }

        $order = $this->create_order->execute($data);

        $this->sendMailOrder($order);

        if ($request['payment_method'] != 1) {
            return $this->payment->excute($order);
        } else {
            Cart::where('uuid', $request->input('cart_id'))->delete();
            return view($this->viewAlert());
        }
    }

    public function paymentResponse()
    {
        $payment_response = session('payment_response');

        if ($payment_response == null) {
            return redirect('/');
        }

        $order = Order::where('cart_id', $payment_response->cart_id)->first();

        if (!$order) {
            return view($this->viewAlert())->with('alert', "Lỗi không tìm thấy đơn hàng ! Xin vui lòng thử lại !");
        }

        $messages = '';

        if ($payment_response->status_code === false) {
            $order->update(['payment_status' => 4]);
        } else {
            $order->update(['payment_status' => 2]);
        }

        Cart::where('uuid', $payment_response->cart_id)->delete();

        return view($this->viewAlert());
    }

    public function sendMailOrder($order)
    {
        $email_noti = OrderMail::whereStatus(1)->get();

        foreach ($email_noti as $email) {
            Mail::to($email->email)->queue(new MailNotify($order));
        }

        return $order;
    }

    public function viewAlert() 
    {
        return 'order::orderAlert';
    }
}
