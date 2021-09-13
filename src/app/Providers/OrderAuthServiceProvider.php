<?php

namespace VCComponent\Laravel\Order\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class OrderAuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        
        Gate::define('manage-order', 'VCComponent\Laravel\Order\Contracts\OrderPolicyInterface@ableToUse');
        Gate::define('manage-order-item', 'VCComponent\Laravel\Order\Contracts\OrderItemPolicyInterface@ableToUse');
        Gate::define('manage-order-mail', 'VCComponent\Laravel\Order\Contracts\OrderMailPolicyInterface@ableToUse');
        //
    }
}
