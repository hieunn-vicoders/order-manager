<?php

namespace VCComponent\Laravel\Order\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use VCComponent\Laravel\Order\Entities\CartItem;

class Cart extends Model
{
    protected $primaryKey = 'uuid';

    protected $fillable = [
        'uuid',
        'total',
    ];

    public $incrementing = false;

    protected $keyType = 'int';

    public function cartItems()
    {
        return $this->hasMany(CartItem::class, 'cart_id', 'uuid');
    }

    public function calculateTotal(): int
    {
        return $this->cartItems->sum('amount');
    }
    public function discount($promo_code)
    {
        if ($promo_code->getPromoType() === 1) {
            return $promo_code->getPromoValue();
        } elseif ($promo_code->promo_type === 2) {
            return $this->calculateTotal() * (($promo_code->getPromoValue()) / 100);
        }
    }
    public function getTotal($promo_code)
    {
        return $this->calculateTotal() - $this->discount($promo_code);
    }

    public function cartTypes()
    {
        return [
            'cart',
        ];
    }

    public function viewCartItems()
    {
        return $this->hasMany(CartItem::class, 'cart_id', 'uuid')->with('product', 'itemAttributes');
    }

    public static function generateUuid()
    {
        return Str::uuid()->toString();
    }
}
