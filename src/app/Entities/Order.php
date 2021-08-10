<?php

namespace VCComponent\Laravel\Order\Entities;

use Illuminate\Database\Eloquent\Model;
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
}
