<?php

namespace App\Models;

use App\Models\Studio\Studio;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RbNotification extends Model
{
    use HasFactory;
    protected $table = "notifications";
    protected $fillable = [
        'user_id',
        'booking_id',
        'studio_id',
        'vendor_id',
        'type',
        'image',
        'title',
        'message',
        'shown_to_user',
        'is_read'
    ];
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    public function booking()
    {
        return $this->hasOne(Booking::class, 'id', 'booking_id')->with('service');
    }
    public function studio()
    {
        return $this->hasOne(Studio::class, 'id', 'studio_id');
    }
}
