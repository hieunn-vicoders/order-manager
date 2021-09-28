<?php

namespace VCComponent\Laravel\Order\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use VCComponent\Laravel\Order\Contracts\OrderItemPolicyInterface;
use VCComponent\Laravel\Order\Contracts\OrderMailPolicyInterface;
use VCComponent\Laravel\Order\Contracts\OrderPolicyInterface;
use VCComponent\Laravel\Order\Entities\Order;
use VCComponent\Laravel\Order\Entities\OrderItem;
use VCComponent\Laravel\Order\Entities\OrderMail;

class OrderAuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        OrderItem::class => OrderItemPolicyInterface::class,
        OrderMail::class => OrderMailPolicyInterface::class,
        Order::class     => OrderPolicyInterface::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        //
    }
}
