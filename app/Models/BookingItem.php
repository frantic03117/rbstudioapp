<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingItem extends Model
{
    use HasFactory;
    public function rents(){
        return $this->belongsTo(Rent::class, 'item_id', 'id');
    }
}
