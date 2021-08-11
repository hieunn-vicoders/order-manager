<?php

Route::prefix(config('order.namespace'))->middleware('web')->group(function () {

    Route::get('cart', 'VCComponent\Laravel\Order\Contracts\ViewCartControllerInterface@index');

    Route::put('cart-items/{id}/quantity', 'VCComponent\Laravel\Order\Http\Controllers\Web\Cart\CartController@changCartItemQuantity')->name('cart-items.change-quantity');
    Route::get('cart-items/{id}', 'VCComponent\Laravel\Order\Http\Controllers\Web\Cart\CartController@deleteCartItem')->name('cart-items.delete');
    Route::post('cart-items', 'VCComponent\Laravel\Order\Http\Controllers\Web\Cart\CartController@createCartItem')->name('cart-items.create');

    Route::get('/order-info', 'VCComponent\Laravel\Order\Contracts\ViewOrderControllerInterface@index');

    Route::post('/order-create', 'VCComponent\Laravel\Order\Http\Controllers\Web\Order\OrderController@create')->name('order.create');

    Route::get('/payment-response', 'VCComponent\Laravel\Order\Http\Controllers\Web\Order\OrderController@paymentResponse');
});
