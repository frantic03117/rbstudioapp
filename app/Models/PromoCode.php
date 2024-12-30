<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Studio\Studio;

class PromoCode extends Model
{
    use HasFactory;
    protected $table = "promo_codes";
    public function studio(){
        return $this->hasOne(Studio::class,'id', 'studio_id');
    }
     public function user(){
        return $this->hasOne(User::class,'id', 'user_id');
    }
}
