<?php

namespace VCComponent\Laravel\Order\Services;

use Illuminate\Support\Facades\Cookie;

class GetCart
{
    protected $entity;
    protected $data = [
        'cart'    => null,
        'fetched' => false,
    ];

    public function __construct()
    {
        if (config('order.models.cart')) {
            $this->entity = config('order.models.cart');
        } else {
            $this->entity = \VCComponent\Laravel\Order\Entities\Cart::class;
        }
        if (Cookie::has('cart')) {
            $cart       = $this->entity::where('uuid', Cookie::get('cart'))->with('cartItems')->first();
            $this->data = ([
                'cart'    => $cart,
                'fetched' => true,
            ]);
        }
    }

    public function getCart()
    {
        if ($this->data['fetched'] === false) {
            $this->fetch();
            return $this->data['cart'];
        }
        return $this->data['cart'];
    }

    public function fetch()
    {
        $cart = [];
        if ($this->data['fetched'] === false) {
            if (Cookie::has('cart')) {
                $cart = $this->entity::where('uuid', Cookie::get('cart'))->with('cartItems')->first();
            }
        }
        $this->data = ([
            'cart'    => $cart,
            'fetched' => true,
        ]);

        return $this->data;
    }
}
