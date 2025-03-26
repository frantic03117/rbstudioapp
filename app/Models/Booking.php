<?php

namespace App\Models;

use App\Models\Studio\Studio;
use App\Models\Studio\Service;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'studio_id',
        'vendor_id',
        'service_id',
        'bill_no',
        'booking_start_date',
        'booking_end_date',
        'start_at',
        'end_at',
        'duration',
        'payment_status',
        'discount',
        'refunded',
        'booking_status',
        'studio_charge',
        'partial_percent',
        'promo_id',
        'promo_code',
        'promo_discount_calculated',
        'approved_at',
        'created_by',
        'created_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function vendor()
    {
        return $this->belongsTo(Vendor::class)
            ->join("users", "users.vendor_id", "=", "vendors.id")
            ->select('vendors.*', 'users.email', 'users.mobile');
    }
    public function studio()
    {
        return $this->belongsTo(Studio::class)->with('images');
    }
    public function rents()
    {
        return $this->belongsToMany(Rent::class, BookingItem::class, 'booking_id', 'item_id')->withPivot('charge', 'uses_hours');
    }
    public function creater()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id', 'id');
    }
    public function gst()
    {
        return $this->hasOne(BookingGst::class, 'id', 'gst_id');
    }
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'booking_id', 'id')->where('status', 'Success');
    }
}