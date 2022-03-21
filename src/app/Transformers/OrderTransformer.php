<?php

namespace VCComponent\Laravel\Order\Transformers;

use League\Fractal\TransformerAbstract;
use VCComponent\Laravel\Order\Entities\OrderItems;
use VCComponent\Laravel\Order\Transformers\OrderItemTransformer;

class OrderTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'orderItems',
        'order_items'
    ];

    public function __construct($includes = [])
    {
        $this->setDefaultIncludes($includes);
    }

    public function transform($model)
    {
        return [
            'id' => (int) $model->id,
            'customer_id' => (int) $model->customer_id,
            'phone_number' => $model->phone_number,
            'email' => $model->email,
            'address' => $model->address,
            'district' => $model->district,
            'province' => $model->province,
            'username' => $model->username,
            'total' => (int) $model->total,
            'order_note' => $model->order_note,
            'payment_method' => (int) $model->payment_method,
            'payment_status' => (int) $model->payment_status,
            'status_id' => (int) $model->status_id,
            'cart_id' => $model->cart_id,
            'order_date' => $model->order_date,
            'timestamps' => [
                'created_at' => $model->created_at,
                'updated_at' => $model->updated_at,
            ],

        ];
    }

    public function includeOrderItems($orders)
    {
        $orderItems = $orders->orderItems;
        return $this->collection($orderItems, new OrderItemTransformer);
    }
}
