<?php
$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function ($api) {
    $api->group(['prefix' => config('order.namespace')], function ($api) {
        $api->group(['prefix' => 'admin'], function ($api) {
            $api->get("/orders/exports", "VCComponent\Laravel\Order\Http\Controllers\Api\Admin\OrderController@export");
            $api->get('/orders/status', 'VCComponent\Laravel\Order\Http\Controllers\Api\Admin\OrderStatusController@index');
            $api->resource("/orders", "VCComponent\Laravel\Order\Http\Controllers\Api\Admin\OrderController");
            $api->put("/orders/{id}/payment-status", "VCComponent\Laravel\Order\Http\Controllers\Api\Admin\OrderController@paymentStatus");
            $api->put("/orders/{id}/status", "VCComponent\Laravel\Order\Http\Controllers\Api\Admin\OrderController@updateStatus");
            $api->put("/order_item/{id}/product", "VCComponent\Laravel\Order\Http\Controllers\Api\Admin\OrderItemController@update");
            $api->delete("/order_item/{id}/product", "VCComponent\Laravel\Order\Http\Controllers\Api\Admin\OrderItemController@destroy");
            $api->put("orderMails/{id}/status", "VCComponent\Laravel\Order\Http\Controllers\Api\Admin\OrderMailController@updateStatus");
            $api->resource("orderMails", "VCComponent\Laravel\Order\Http\Controllers\Api\Admin\OrderMailController");
        });
        $api->put("/cart_item/{id}/quantity", "VCComponent\Laravel\Order\Http\Controllers\Api\Fontend\Cart\CartItemController@changeQuantity");
        $api->get('/users/{id}/orders', 'VCComponent\Laravel\Order\Http\Controllers\Api\Fontend\OrderController@index');
        $api->get('/orders/{id}', 'VCComponent\Laravel\Order\Http\Controllers\Api\Fontend\OrderController@show');
        $api->post('/orders', 'VCComponent\Laravel\Order\Http\Controllers\Api\Fontend\OrderController@store');
    });
});
