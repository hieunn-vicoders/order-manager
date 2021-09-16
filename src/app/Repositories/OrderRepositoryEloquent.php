<?php

namespace VCComponent\Laravel\Order\Repositories;

use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use VCComponent\Laravel\Order\Entities\Cart;
use VCComponent\Laravel\Order\Entities\Order;
use VCComponent\Laravel\Order\Entities\OrderStatus;
use VCComponent\Laravel\Order\Repositories\OrderRepository;
use VCComponent\Laravel\User\Entities\User;
use VCComponent\Laravel\Vicoders\Core\Exceptions\NotFoundException;

/**
 * Class AccountantRepositoryEloquent.
 */
class OrderRepositoryEloquent extends BaseRepository implements OrderRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        if (isset(config('order.models')['order'])) {
            return config('order.models.order');
        } else {
            return Post::class;
        }
    }

    public function getEntity()
    {
        return $this->model;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    public function findById($id)
    {
        $order = $this->model->find($id);
        if (!$order) {
            throw new NotFoundException('Order');
        }
        return $order;
    }

    public function updateStatus($request, $id)
    {
        $updateStatus = $this->find($id);
        $status = OrderStatus::where('status_id', $request->input('status_id'))->first();
        $updateStatus->status_id = $request->input('status_id');
        if (!$status) {
            throw new NotFoundException('Order Status');
        }
        $updateStatus->save();

    }

    public function paymentStatus($request, $id)
    {
        $paymentStatus = $this->find($id);
        $paymentStatus->payment_status = $request->input('payment_status');
        $paymentStatus->save();
    }
    public function getPromoPrice($promotion, $order, $total)
    {
        if ($promotion->isApplicableProduct()) {
            $total_price_promo = 0;
            foreach ($order->orderItems as $order_item) {
                $price_promo = 0;
                if ($promotion->isProduct($order_item->product_id)) {
                    $price_promo = $order_item->discount($promotion);
                }
                $total_price_promo += $price_promo;
            }
            $total -= $total_price_promo;
        } else {
            $total = $order->getTotal($promotion);
        }
        return $total;

    }
    public function usePromoCode($request, $order, $total)
    {
        if ($request->has('promo_code')) {
            $promotion = Promotion::check($request->promo_code);
            if ($promotion) {
                if ($promotion->getType() == 'users') {
                    $user = User::where('phone_number', $request->phone_number)->first();
                    if ($user && $promotion->isUser($user->id)) {
                        $total = $this->getPromoPrice($promotion, $order, $total);
                    }
                } else {
                    $total = $this->getPromoPrice($promotion, $order, $total);
                }
            }
        }
        return $total;
    }
    public function getPromoPriceWeb($promotion, $cart, $total)
    {
        if ($promotion->isApplicableProduct()) {
            $total_price_promo = 0;
            foreach ($cart->cartItems as $cart_item) {
                $price_promo = 0;
                if ($promotion->isProduct($cart_item->product_id)) {
                    $price_promo = $cart_item->discount($promotion);
                }
                $total_price_promo += $price_promo;
            }
            $total -= $total_price_promo;
        } else {
            $total = $cart->getTotal($promotion);
        }
        return $total;
    }
    public function usePromoCodeWeb($request)
    {
        $total = $request->input('total');
        if ($request->has('promo_code')) {
            $promotion = Promotion::check($request->input('promo_code'));
            $cart = Cart::find($request->input('cart_id'));
            if ($promotion) {
                if ($promotion->getType() == 'users') {

                    if (auth()->check() && $promotion->isUser(auth()->user()->id)) {
                        $total = $this->getPromoPriceWeb($promotion, $cart, $total);
                    }
                } else {
                    $total = $this->getPromoPriceWeb($promotion, $cart, $total);
                }
            }

        }
        return $total;
    }

}
