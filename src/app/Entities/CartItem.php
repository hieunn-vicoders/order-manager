<?php

namespace VCComponent\Laravel\Order\Entities;

use Illuminate\Database\Eloquent\Model;
use VCComponent\Laravel\Order\Entities\Cart;
use VCComponent\Laravel\Order\Entities\CartProductAttribute;
use VCComponent\Laravel\Order\Traits\HasCartProductAttributes;
use VCComponent\Laravel\Product\Entities\Product;

class CartItem extends Model
{
    use HasCartProductAttributes;

    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'price',
        'amount',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class, 'cart_id', 'uuid');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function calculateAmount(): int
    {
        return $this->quantity * $this->price;
    }

    public function cartProductAttributes()
    {
        return $this->hasMany(CartProductAttribute::class);
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
