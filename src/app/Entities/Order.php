<?php

namespace VCComponent\Laravel\Order\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use VCComponent\Laravel\Order\Entities\OrderItem;
use VCComponent\Laravel\Order\Traits\Helpers;
use VCComponent\Laravel\Payment\Entities\PaymentMethod;

class Order extends Model
{
    use Helpers;

    protected $fillable = [
        'customer_id',
        'phone_number',
        'username',
        'email',
        'address',
        'district',
        'province',
        'total',
        'order_note',
        'payment_method',
        'payment_status',
        'status_id',
        'cart_id',
        'order_date',
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method');
    }

    public function orderTypes()
    {
        return [
            'order',
        ];
    }
    public function ableToUse($user)
    {
        return true;
    }
    public function calculateTotal()
    {
        return $this->orderItems()->sum(DB::raw('price * quantity'));
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
}
