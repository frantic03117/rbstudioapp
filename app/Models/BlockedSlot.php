<?php

namespace App\Models;

use App\Models\Studio\Studio;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockedSlot extends Model
{
    use HasFactory;
    protected $fillable = [
        'slot_id',
        'studio_id',
        'booking_id',
        'reason',
        'bdate',
        'created_at'
    ];
    public function slot()
    {
        return $this->hasOne(Slot::class, 'id', 'slot_id');
    }
    public function studio()
    {
        return $this->hasOne(Studio::class, 'id', 'studio_id');
    }
}
