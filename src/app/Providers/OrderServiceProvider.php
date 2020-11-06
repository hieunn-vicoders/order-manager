<?php

namespace VCComponent\Laravel\Order\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use VCComponent\Laravel\Order\Contracts\ViewCartControllerInterface;
use VCComponent\Laravel\Order\Contracts\ViewOrderControllerInterface;
use VCComponent\Laravel\Order\Events\AddAttributesEvent;
use VCComponent\Laravel\Order\Http\Controllers\Web\Cart\CartController;
use VCComponent\Laravel\Order\Http\Controllers\Web\Order\OrderController;
use VCComponent\Laravel\Order\Http\Middleware\CheckCart;
use VCComponent\Laravel\Order\Http\View\Composers\CartAttributesComposer;
use VCComponent\Laravel\Order\Http\View\Composers\CartComposer;
use VCComponent\Laravel\Order\Repositories\CartItemRepository;
use VCComponent\Laravel\Order\Repositories\CartItemRepositoryEloquent;
use VCComponent\Laravel\Order\Repositories\CartRepository;
use VCComponent\Laravel\Order\Repositories\CartRepositoryEloquent;
use VCComponent\Laravel\Order\Repositories\OrderItemRepository;
use VCComponent\Laravel\Order\Repositories\OrderItemRepositoryEloquent;
use VCComponent\Laravel\Order\Repositories\OrderMailRepository;
use VCComponent\Laravel\Order\Repositories\OrderMailRepositoryEloquent;
use VCComponent\Laravel\Order\Repositories\OrderRepository;
use VCComponent\Laravel\Order\Repositories\OrderRepositoryEloquent;
use VCComponent\Laravel\Order\Services\GetCart;

class OrderServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('getCart', GetCart::class);
        $this->app->bind(OrderRepository::class, OrderRepositoryEloquent::class);
        $this->app->bind(OrderItemRepository::class, OrderItemRepositoryEloquent::class);
        $this->app->bind(CartItemRepository::class, CartItemRepositoryEloquent::class);
        $this->app->bind(CartRepository::class, CartRepositoryEloquent::class);
        $this->app->bind(OrderMailRepository::class, OrderMailRepositoryEloquent::class);
        $this->registerControllers();
    }

    public function boot(Router $router)
    {
        $router->pushMiddlewareToGroup('web', CheckCart::class);

        $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'order');
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->publishes([
            __DIR__ . '/../../config/order.php'           => config_path('order.php'),
            __DIR__ . '/../../resources/sass/cart.scss'   => base_path('/resources/sass/orders/cart.scss'),
            __DIR__ . '/../../../dist/js/app.js'          => base_path('/resources/js/vendor/webpress/order.js'),
            __DIR__ . '/../../resources/sass/cart.png'    => public_path('/images/cart/cart.png'),
            __DIR__ . '/../../resources/sass/tick.png'    => public_path('/images/cart/tick.png'),
        ]);

        View::composer(['order::cartIcon', 'order::cart', 'order::orderInfo'], CartComposer::class);
        View::composer('order::cart', CartAttributesComposer::class);
    }

    private function registerControllers()
    {
        $this->app->bind(ViewOrderControllerInterface::class, OrderController::class);
        $this->app->bind(ViewCartControllerInterface::class, CartController::class);
    }
}
