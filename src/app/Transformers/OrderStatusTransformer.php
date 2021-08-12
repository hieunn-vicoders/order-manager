<?php

namespace VCComponent\Laravel\Order\Transformers;

use League\Fractal\TransformerAbstract;
use VCComponent\Laravel\Order\Entities\OrderStatus;

class OrderStatusTransformer extends TransformerAbstract
{

    public function __construct()
    {
    }

    public function transform(OrderStatus $model)
    {
        return [
            'name' => $model->name,
            'slug' => $model->slug,
            'status' => $model->status,
        ];
    }
}
