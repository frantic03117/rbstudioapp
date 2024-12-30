<?php

namespace App\Models\Studio;

use App\Models\Rent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Charge extends Model
{
    use HasFactory;
    protected $fillable = [
        'studio_id', 'type', 'item_id', 'charge', 'created_at'
    ] ;
    public function item(){
        return $this->belongsTo(Rent::class);
    }
}
