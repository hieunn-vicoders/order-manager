<?php

namespace VCComponent\Laravel\Order\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use VCComponent\Laravel\Order\Contracts\OrderItemPolicyInterface;
use VCComponent\Laravel\Order\Contracts\OrderMailPolicyInterface;
use VCComponent\Laravel\Order\Contracts\OrderPolicyInterface;
use VCComponent\Laravel\Order\Contracts\OrderStatusPolicyInterface;
use VCComponent\Laravel\Order\Contracts\ViewCartControllerInterface;
use VCComponent\Laravel\Order\Contracts\ViewOrderControllerInterface;
use VCComponent\Laravel\Order\Events\AddAttributesEvent;
use VCComponent\Laravel\Order\Http\Controllers\Web\Cart\CartController;
use VCComponent\Laravel\Order\Http\Controllers\Web\Order\OrderController;
use VCComponent\Laravel\Order\Http\Middleware\CheckCart;
use VCComponent\Laravel\Order\Http\View\Composers\CartAttributesComposer;
use VCComponent\Laravel\Order\Http\View\Composers\CartComposer;
use VCComponent\Laravel\Order\Policies\OrderItemPolicy;
use VCComponent\Laravel\Order\Policies\OrderMailPolicy;
use VCComponent\Laravel\Order\Policies\OrderPolicy;
use VCComponent\Laravel\Order\Policies\OrderStatusPolicy;
use VCComponent\Laravel\Order\Repositories\CartItemRepository;
use VCComponent\Laravel\Order\Repositories\CartItemRepositoryEloquent;
use VCComponent\Laravel\Order\Repositories\CartRepository;
use VCComponent\Laravel\Order\Repositories\CartRepositoryEloquent;
use VCComponent\Laravel\Order\Repositories\OrderItemRepository;
use VCComponent\Laravel\Order\Repositories\OrderItemRepositoryEloquent;
use VCComponent\Laravel\Order\Repositories\OrderStatusRepository;
use VCComponent\Laravel\Order\Repositories\OrderStatusRepositoryEloquent;
use VCComponent\Laravel\Order\Repositories\OrderMailRepository;
use VCComponent\Laravel\Order\Repositories\OrderMailRepositoryEloquent;
use VCComponent\Laravel\Order\Repositories\OrderRepository;
use VCComponent\Laravel\Order\Repositories\OrderRepositoryEloquent;
use VCComponent\Laravel\Order\Services\GetCart;
use VCComponent\Laravel\Order\Validators\OrderItemValidator;
use VCComponent\Laravel\Order\Validators\OrderItemValidatorInterface;
use VCComponent\Laravel\Order\Validators\OrderValidator;
use VCComponent\Laravel\Order\Validators\OrderValidatorInterface;

class OrderServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('getCart', GetCart::class);
        $this->app->bind(OrderRepository::class, OrderRepositoryEloquent::class);
        $this->app->bind(OrderItemRepository::class, OrderItemRepositoryEloquent::class);
        $this->app->bind(OrderStatusRepository::class, OrderStatusRepositoryEloquent::class);
        $this->app->bind(CartItemRepository::class, CartItemRepositoryEloquent::class);
        $this->app->bind(CartRepository::class, CartRepositoryEloquent::class);
        $this->app->bind(OrderMailRepository::class, OrderMailRepositoryEloquent::class);
        $this->registerControllers();
        $this->registerPolicies();
        $this->registerValidators();

        $this->app->register(OrderAuthServiceProvider::class);
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
            __DIR__ . '/../../resources/js/cart.js'       => base_path('/resources/js/order/cart.js'),
            __DIR__ . '/../../resources/js/order-info.js' => base_path('/resources/js/order/order-info.js'),
            __DIR__ . '/../../resources/sass/cart.png'    => public_path('/images/cart/cart.png'),
            __DIR__ . '/../../resources/sass/tick.png'    => public_path('/images/cart/tick.png'),
            __DIR__ . '/../../database/seeds/OrderSeeder.php'  => base_path('/database/seeds/OrderSeeder.php'),
            __DIR__ . '/../../database/seeds/OrderItemsSeeder.php'  => base_path('/database/seeds/OrderItemsSeeder.php'),
            __DIR__ . '/../../database/seeds/OrderStatusSeeder.php'  => base_path('/database/seeds/OrderStatusSeeder.php'),
        ]);

        View::composer(['order::cartIcon', 'order::cart', 'order::orderInfo'], CartComposer::class);
        View::composer('order::cart', CartAttributesComposer::class);
    }

    private function registerControllers()
    {
        $this->app->bind(ViewOrderControllerInterface::class, OrderController::class);
        $this->app->bind(ViewCartControllerInterface::class, CartController::class);
    }

    private function registerPolicies()
    {
        $this->app->bind(OrderPolicyInterface::class, OrderPolicy::class);
        $this->app->bind(OrderItemPolicyInterface::class, OrderItemPolicy::class);
        $this->app->bind(OrderMailPolicyInterface::class, OrderMailPolicy::class);
    }

    private function registerValidators()
    {
        $this->app->bind(OrderValidatorInterface::class, OrderValidator::class);
        $this->app->bind(OrderItemValidatorInterface::class, OrderItemValidator::class);
    }
}
