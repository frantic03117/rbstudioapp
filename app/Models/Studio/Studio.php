<?php

namespace App\Models\Studio;

use App\Models\Location\City;
use App\Models\Location\Country;
use App\Models\Location\State;
use App\Models\Rent;
use App\Models\ServiceStudio;
use App\Models\StudioImage;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Studio extends Model
{
    use HasFactory;

    public function vendor(): HasOne
    {
        return $this->hasOne(Vendor::class, "id", "vendor_id")
            ->join("users", "users.vendor_id", "=", "vendors.id")->where('role', 'Admin')
            ->select('vendors.*', 'users.email', 'users.mobile');
    }
    public function country(): HasOne
    {
        return $this->hasOne(Country::class, "id", "country_id");
    }
    public function state(): HasOne
    {
        return $this->hasOne(State::class, "id", "state_id");
    }
    public function district(): HasOne
    {
        return $this->hasOne(City::class, "id", "district_id");
    }
    public function images(): HasMany
    {
        return $this->hasMany(StudioImage::class, "studio_id", "id");
    }

    public function products()
    {
        return $this->belongsToMany(Rent::class, Charge::class, 'studio_id', 'item_id')->where('type', '=', 'Item')->as('resources')->withPivot(['charge', 'created_at']);
    }
    public function charges()
    {
        return $this->belongsToMany(Service::class, ServiceStudio::class, 'studio_id', 'service_id')->as('charge')->withPivot(['charge', 'is_permissable', 'id']);
    }
    public function services()
    {
        return $this->belongsToMany(Service::class, ServiceStudio::class, 'studio_id', 'service_id')->as('charge')->withPivot(['charge', 'is_permissable', 'id']);
    }
}
