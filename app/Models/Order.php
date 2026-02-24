<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'customer',
        'customer_name',
        'customer_phone',
        'customer_address',
        'service',
        'estimated_date',
        'quantity',
        'total_price',
        'status',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer')->where('role', 'customer');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service');
    }
}
