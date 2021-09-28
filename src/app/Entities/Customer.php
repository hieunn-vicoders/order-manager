<?php

namespace VCComponent\Laravel\Order\Entities;

use Illuminate\Database\Eloquent\Model;
use VCComponent\Laravel\Order\Entities\Order;

class Customer extends Model
{
    protected $fillable = [
        'user_id', 'name', 'email', 'phone_number', 'order_count', 'total_amount',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
