<?php

namespace VCComponent\Laravel\Order\Entities;

use Illuminate\Database\Eloquent\Model;

class OrderMail extends Model
{
    protected $fillable = [
        'email',
        'status',
    ];
    public function ableToUse($user)
    {
        return true;
    }
}
