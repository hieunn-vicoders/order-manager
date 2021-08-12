<?php

namespace VCComponent\Laravel\Order\Actions\Order;

use VCComponent\Laravel\Order\Entities\OrderProductAttribute;

class CreateOrderItemAttributesAction
{
    public function excute($order_item, $cart_attribute)
    {
        $data = [
            'order_item_id' => $order_item->id,
            'product_id' => $order_item->product_id,
            'value_id' => $cart_attribute->value_id,
        ];

        $order_attribute_item = OrderProductAttribute::create($data);
        return $order_attribute_item->refresh();
    }

}
