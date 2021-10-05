<?php

namespace VCComponent\Laravel\Order\Http\Controllers\Api\Admin;

use Complex\Exception;
use Illuminate\Http\Request;
use VCComponent\Laravel\Order\Entities\OrderStatus;
use VCComponent\Laravel\Order\Repositories\OrderStatusRepository;
use VCComponent\Laravel\Order\Transformers\OrderStatusTransformer;
use VCComponent\Laravel\Vicoders\Core\Controllers\ApiController;

class OrderStatusController extends ApiController
{
    protected $repository;

    public function __construct(OrderStatusRepository $repository)
    {
        $this->repository  = $repository;
        $this->entity      = $repository->getEntity();
        $this->transformer = OrderStatusTransformer::class;
        if (config('order.auth_middleware.admin.middleware') !== '') {
            $this->middleware(
                config('order.auth_middleware.admin.middleware'),
                ['except' => config('order.auth_middleware.admin.middleware.except')]
            );
        }
        else{
            throw new Exception("Admin middleware configuration is required");
        }
    }

    public function index()
    {
        return $this->response->item($this->entity->get(), new $this->transformer);
    }

}
