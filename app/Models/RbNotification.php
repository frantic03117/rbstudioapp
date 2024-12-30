<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RbNotification extends Model
{
    use HasFactory;
    protected $table = "notifications";
    public function user(){
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
