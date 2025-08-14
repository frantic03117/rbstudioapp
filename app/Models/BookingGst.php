<?php

namespace App\Models;

use App\Models\Location\City;
use App\Models\Location\State;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingGst extends Model
{
    use HasFactory;
    protected $table = "booking_gsts";
    protected $fillable = [
        'booking_id',
        'user_id',
        'gst',
        'address',
        'country_id',
        'state_id',
        'city_id',
        'pincode',
        'updated_at',
        'company'
    ];
    public function state()
    {
        return $this->hasOne(State::class, 'id', 'state_id');
    }
    public function city()
    {
        return $this->hasOne(City::class, 'id', 'city_id');
    }
}
