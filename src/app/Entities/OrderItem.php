<?php

namespace VCComponent\Laravel\Order\Entities;

use Illuminate\Database\Eloquent\Model;
use VCComponent\Laravel\Order\Traits\Helpers;
use VCComponent\Laravel\Product\Entities\Product;

class OrderItem extends Model
{

    protected $fillable = [
        'product_id',
        'quantity',
        'price',
        'order_id',
    ];

    public function product()
    {
        $model_product = config('product.models.product');
        return $this->belongsTo($model_product, 'product_id');
    }
}
