<?php

return [

    'namespace'       => env('ORDER_COMPONENT_NAMESPACE', 'order-management'),

    'models'          => [
        'order'     => VCComponent\Laravel\Order\Entities\Order::class,
        'cart'      => VCComponent\Laravel\Order\Entities\Cart::class,
        'cartItem'  => VCComponent\Laravel\Order\Entities\CartItem::class,
        'orderItem' => VCComponent\Laravel\Order\Entities\OrderItem::class,
    ],

    'transformers'    => [
        'order' => VCComponent\Laravel\Order\Transformers\OrderTransformer::class,
    ],

    'viewModels'      => [
        'cart'  => VCComponent\Laravel\Order\ViewModels\Cart\CartViewModel::class,
        'order' => VCComponent\Laravel\Order\ViewModels\Order\OrderViewModel::class,
    ],

    'auth_middleware' => [
        'admin'    => [
            'middleware' => '',
            'except'     => [],
        ],
        'frontend' => [
            'middleware' => '',
            'except'     => [],
        ],
    ],
    'test_mode' => false
];
