<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'transaction_date',
        'transaction_id',
        'type',
        'amount',
        'order_id',
        'gateway_order_id',
        'user_id',
        'booking_id',
        'studio_id',
        'vendor_id',
        'init_resp',
        'ret_resp',
        'mode',
        'status',
        'req_by',
    ];
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    public function vendor()
    {
        return $this->hasOne(Vendor::class, 'id', 'vendor_id');
    }
    public function booking()
    {
        return $this->hasOne(Booking::class, 'id', 'booking_id');
    }
}
