<?php

namespace VCComponent\Laravel\Order\Test;

use Cviebrock\EloquentSluggable\ServiceProvider;
use Dingo\Api\Provider\LaravelServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use VCComponent\Laravel\Order\Providers\OrderServiceProvider;
use VCComponent\Laravel\Product\Providers\ProductRouteProvider;
use VCComponent\Laravel\Product\Providers\ProductServiceProvider;
use VCComponent\Laravel\User\Providers\UserComponentProvider;

class TestCase extends OrchestraTestCase
{
    /**
     * Load package service provider
     *
     * @param  \Illuminate\Foundation\Application $app
     *
     * @return \VCComponent\Laravel\Order\Providers\OrderComponentProvider
     */
    protected function getPackageProviders($app)
    {
        return [
            ProductServiceProvider::class,
            OrderServiceProvider::class,
            UserComponentProvider::class,
            LaravelServiceProvider::class,
            ServiceProvider::class,
        ];
    }

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->withFactories(__DIR__ . '/../src/database/factories');
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.key', 'base64:TEQ1o2POo+3dUuWXamjwGSBx/fsso+viCCg9iFaXNUA=');
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        $app['config']->set('order.namespace', 'order-management');
        $app['config']->set('order.models', [
            'order' => \VCComponent\Laravel\Order\Entities\Order::class,
        ]);
        $app['config']->set('order.transformers', [
            'order' => \VCComponent\Laravel\Order\Transformers\OrderTransformer::class,
        ]);
        $app['config']->set('order.auth_middleware', [
            'admin'    => [],
            'frontend' => [],
        ]);
        $app['config']->set('order.test_mode', true);
        $app['config']->set('api', [
            'standardsTree'      => 'x',
            'subtype'            => '',
            'version'            => 'v1',
            'prefix'             => 'api',
            'domain'             => null,
            'name'               => null,
            'conditionalRequest' => true,
            'strict'             => false,
            'debug'              => true,
            'errorFormat'        => [
                'message'     => ':message',
                'errors'      => ':errors',
                'code'        => ':code',
                'status_code' => ':status_code',
                'debug'       => ':debug',
            ],
            'middleware'         => [
            ],
            'auth'               => [
            ],
            'throttling'         => [
            ],
            'transformer'        => \Dingo\Api\Transformer\Adapter\Fractal::class,
            'defaultFormat'      => 'json',
            'formats'            => [
                'json' => \Dingo\Api\Http\Response\Format\Json::class,
            ],
            'formatsOptions'     => [
                'json' => [
                    'pretty_print' => false,
                    'indent_style' => 'space',
                    'indent_size'  => 2,
                ],
            ],
        ]);
    }
}
