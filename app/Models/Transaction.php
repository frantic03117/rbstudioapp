<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    public function user(){
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    public function vendor(){
        return $this->hasOne(Vendor::class, 'id', 'vendor_id');
    }
    public function booking(){
        return $this->hasOne(Booking::class, 'id', 'booking_id');
    }
}
