<?php

namespace VCComponent\Laravel\Order\Entities;

use Illuminate\Database\Eloquent\Model;
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
    public function ableToUse($user)
    {
        return true;
    }
    public function calculateAmount()
    {
        return $this->quantity * $this->price;
    }
    public function discount($promo_code)
    {
        if ($promo_code->getPromoType() === 1) {
            return $promo_code->getPromoValue() * $this->quantity;
        } elseif ($promo_code->promo_type === 2) {
            return $this->calculateAmount() * (($promo_code->getPromoValue()) / 100);
        }
    }
    public function getTotal($promo_code)
    {
        return $this->calculateAmount() - $this->discount($promo_code);
    }
}
