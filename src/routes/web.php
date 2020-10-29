<?php

Route::prefix(config('order.namespace'))->middleware('web')->group(function () {

    Route::get('cart', 'VCComponent\Laravel\Order\Contracts\ViewCartControllerInterface@index');

    Route::put('cart-items/{id}/quantity', 'VCComponent\Laravel\Order\Http\Controllers\Web\Cart\ChangeCartItemQuantityController')->name('cart-items.change-quantity');
    Route::get('cart-items/{id}', 'VCComponent\Laravel\Order\Http\Controllers\Web\Cart\DeleteCartItemController')->name('cart-items.delete');
    Route::post('cart-items', 'VCComponent\Laravel\Order\Http\Controllers\Web\Cart\CreateCartItemController')->name('cart-items.create');

    Route::get('/order-info', 'VCComponent\Laravel\Order\Contracts\ViewOrderControllerInterface@index')->name("order.back");
    Route::post('/payment-info', 'VCComponent\Laravel\Order\Contracts\ViewOrderControllerInterface@paymentInfo')->name('order.payment');

    Route::post('/order-create', 'VCComponent\Laravel\Order\Http\Controllers\Web\Order\CreateOrderController@create')->name('order.create');

    Route::get('/payment-response', 'VCComponent\Laravel\Order\Http\Controllers\Web\Order\CreateOrderController@paymentResponse');
});
